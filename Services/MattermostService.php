<?php
/**
 * Mattermost service
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Creatissimo\MattermostBundle\Services;

use Creatissimo\MattermostBundle\Entity\Attachment;
use Creatissimo\MattermostBundle\Entity\AttachmentField;
use Creatissimo\MattermostBundle\Entity\Message;
use phpDocumentor\Reflection\Types\Self_;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

/**
 * Class MattermostService
 * @package Creatissimo\MattermostBundle\Services
 */
class MattermostService
{
    private const API_BASE_PATH      = '/api/v4/';
    private const API_ENDPOINT_POSTS = self::API_BASE_PATH . 'posts';

    private string          $environment;
    private LoggerInterface $logger;
    private string          $url;
    private string          $botAccessToken;
    private array           $configuration             = [];
    private array           $environmentConfigurations = [];
    private Message         $message;

    const MAX_MESSAGE_LENGTH = 7600;
    const MAX_TEXT_LENGTH    = 3000;
    const CUT_LENGTH         = 1000;

    public function __construct(string $environment, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->setEnvironment($environment);
    }

    public function setMessage(Message $message, bool $setEnvironmentToMessage = true): self
    {
        $this->message = $message;
        if ($setEnvironmentToMessage) {
            $this->setDefaultsToMessage();
        }

        return $this;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function setDefaultsToMessage(bool $force = false): self
    {
        if ($this->message) {
            $conf = $this->getConfiguration();
            if ($conf && is_array($conf)) {
                if (array_key_exists('channel', $conf) && ($force || (!$force && !$this->message->getChannel()))) {
                    $this->message->setChannel($conf['channel']);
                }
            }
        }

        return $this;
    }

    /**
     * Format the JSON message to post to Mattermost
     */
    protected function serializeMessage(): ?string
    {
        if (!$this->message) {
            return false;
        }

        $messageArray = ['message' => $this->message->getText()];

        if ($this->message->getChannel()) {
            $messageArray['channel_id'] = $this->message->getChannel();
        }

        if ($this->message->hasAttachments()) {
            /** @var Attachment $attachment */
            foreach ($this->message->getAttachments() as $attachment) {
                $attachmentArray = ['title' => $attachment->getTitle()];
                if ($attachment->getFallback()) {
                    $attachmentArray['fallback'] = $attachment->getFallback();
                }
                if ($attachment->getColor()) {
                    $attachmentArray['color'] = $attachment->getColor();
                }
                if ($attachment->getPretext()) {
                    $attachmentArray['pretext'] = $attachment->getPretext();
                }

                if ($attachment->hasFields()) {
                    /** @var AttachmentField $field */
                    foreach ($attachment->getFields() as $field) {
                        $attachmentArray['fields'][] = [
                            'title' => $field->getTitle(),
                            'value' => $field->getValue(),
                            'short' => $field->getShort(),
                        ];
                    }
                }

                $messageArray['attachments'][] = $attachmentArray;
            }
        }

        return json_encode($messageArray);
    }

    public function sendMessage(string $text): bool
    {
        if (!empty($text)) {
            return $this->setMessage(new Message($text))->send();
        }

        return false;
    }

    public function sendMessageToChannel(string $text, string $channel): bool
    {
        if (!empty($text)) {
            return $this->setMessage((new Message($text))->setChannel($channel))->send();
        }

        return false;
    }

    /**
     * Do an HTTP post to Mattermost
     */
    public function send(): bool
    {
        if (!$this->getMessage()) {
            return false;
        }

        $this->processEnvironment();
        $url     = $this->getUrl() . self::API_ENDPOINT_POSTS;
        $message = $this->serializeMessage();
        [$httpStatusCode, $response] = $this->sendCurlRequest($url, $message);

        if (400 === $httpStatusCode) {
            $this->cutCurrentMessage();
            $message = $this->serializeMessage();
            [$httpStatusCode, $response] = $this->sendCurlRequest($url, $message);
        }

        if ($httpStatusCode !== 200) {
            $this->log('Failed to post to mattermost: status ' . $httpStatusCode . '; Message: ' . $message . ' (' . $response . ')');

            return false;
        }
        if ($response !== 'ok') {
            $this->log('Didn\'t get an "ok" back from mattermost, got: ' . $response);

            return false;
        }

        return true;
    }

    protected function log(string $message)
    {
        if (!empty($this->logger)) {
            $this->logger->info($message);
        }
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getBotAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setBotAccessToken(string $botAccessToken): self
    {
        $this->accessToken = $botAccessToken;

        return $this;
    }

    public function getEnvironmentConfigurations(): array
    {
        return $this->environmentConfigurations;
    }

    public function setEnvironmentConfigurations(array $environmentConfigurations): self
    {
        $this->environmentConfigurations = $environmentConfigurations;

        return $this;
    }

    public function getEnvironmentConfiguration(): ?array
    {
        return array_key_exists($this->getEnvironment(), $this->environmentConfigurations)
            ? $this->environmentConfigurations[ $this->getEnvironment() ]
            : null;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $conf): self
    {
        $this->configuration = $conf;

        return $this;
    }

    private function processEnvironment(): void
    {
        $config = $this->getEnvironmentConfiguration();
        if (!empty($config)) {
            $names = ['url', 'botAccessToken'];
            foreach ($names as $name) {
                if (array_key_exists($name, $config)) {
                    $funcName = "set" . ucfirst($name);
                    $this->$funcName($config[ $name ]);
                }
            }

            $names = ['channel'];
            foreach ($names as $name) {
                if (array_key_exists($name, $config)) {
                    $funcName = 'set' . ucfirst($name);
                    $this->message->$funcName($config[ $name ]);
                }
            }
        }
    }

    public function isEnabled(?string $function = null): bool
    {
        $enabled = false;
        $config  = $this->getEnvironmentConfiguration();

        if (!empty($config)) {
            if ($config['enable']) {
                $enabled = true;
            }

            if (
                $function && array_key_exists($function, $config)
                && array_key_exists('enable', $config[ $function ])
                && !$config[ $function ]['enable']
            ) {
                $enabled = false;
            }
        }

        return $enabled;
    }

    private function sendCurlRequest(string $url, string $message): ?array
    {
        $ch = curl_init();
        if (!$ch) {
            $this->log('Failed to create curl handle');

            return [false, false];
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->getBotAccessToken(),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($message)]
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        $response       = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpStatusCode, $response];
    }

    private function cutCurrentMessage(): void
    {
        $text = $this->message->getText();
        if (strlen($text) > self::MAX_TEXT_LENGTH) {
            $text = substr($text, 0, self::MAX_TEXT_LENGTH) . '...';
            $this->message->setText($text);
        }
        if (strlen($this->serializeMessage()) > self::MAX_MESSAGE_LENGTH) {
            $attachments = $this->message->getAttachments();
            if (count($attachments) > 1) {
                rewind($attachments);
                $this->message->setAttachments([current($attachments)]);
            }
            if (strlen($this->serializeMessage()) > self::MAX_MESSAGE_LENGTH) {
                /** @var Attachment $firstAttachment */
                $firstAttachment = current($this->message->getAttachments());
                $firstAttachment->setFallback(substr($firstAttachment->getFallback(), 0, self::CUT_LENGTH));
                $firstAttachment->setTitle(substr($firstAttachment->getTitle(), 0, self::CUT_LENGTH));
                $this->message->setAttachments([$firstAttachment]);
            }
            if (strlen($this->serializeMessage()) > self::MAX_MESSAGE_LENGTH) {
                $firstAttachment = current($this->message->getAttachments());
                $fields          = $firstAttachment->getFields();
                /**
                 * @var int             $key
                 * @var AttachmentField $field
                 */
                foreach ($fields as $key => $field) {
                    if (strlen($field->getValue()) > self::CUT_LENGTH) {
                        $fields[ $key ]->setValue(substr($field->getValue(), 0, self::CUT_LENGTH) . '...');
                    }
                }
                $firstAttachment->setFields($fields);
            }
        }
    }
}
