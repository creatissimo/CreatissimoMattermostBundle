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
use Psr\Log\LoggerInterface;

/**
 * Class MattermostService
 * @package Creatissimo\MattermostBundle\Services
 */
class MattermostService
{
    /** @var string */
    private $environment;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $webhook;

    /** @var string */
    private $appname;

    /** @var array */
    private $configuration;

    /** @var array */
    private $environmentConfigurations;

    /** @var Message */
    private $message;

    const MAX_MESSAGE_LENGTH = 7600;
    const MAX_TEXT_LENGTH    = 3000;
    const CUT_LENGTH         = 1000;

    /**
     * @param string          $environment
     * @param LoggerInterface $logger
     */
    public function __construct($environment, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->setEnvironment($environment);
    }

    /**
     * @param Message $message
     * @param boolean $setEnvironmentToMessage
     *
     * @return $this
     */
    public function setMessage(Message $message, $setEnvironmentToMessage = true)
    {
        $this->message = $message;
        if ($setEnvironmentToMessage) {
            $this->setDefaultsToMessage();
        }

        return $this;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param bool $force
     *
     * @return $this
     */
    public function setDefaultsToMessage($force = false)
    {
        if ($this->message) {
            $conf = $this->getConfiguration();
            if ($conf && is_array($conf)) {
                if (array_key_exists('username', $conf) && ($force || (!$force && !$this->message->getUsername()))) {
                    $this->message->setUsername($conf['username']);
                }

                if (array_key_exists('iconUrl', $conf) && ($force || (!$force && !$this->message->getIconUrl()))) {
                    $this->message->setIconUrl($conf['iconUrl']);
                }

                if (array_key_exists('channel', $conf) && ($force || (!$force && !$this->message->getChannel()))) {
                    $this->message->setChannel($conf['channel']);
                }
            }
        }

        return $this;
    }

    /**
     * Format the JSON message to post to Mattermost
     *
     * @return null|string
     */
    protected function serializeMessage()
    {
        if (!$this->message) {
            return false;
        }

        $messageArray = ['text' => $this->message->getText()];

        if ($this->message->getChannel()) {
            $messageArray['channel'] = $this->message->getChannel();
        }
        if ($this->message->getUsername()) {
            $messageArray['username'] = $this->message->getUsername();
        }
        if ($this->message->getIconUrl()) {
            $messageArray['icon_url'] = $this->message->getIconUrl();
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

    /**
     * @return bool
     * @var string $text
     *
     */
    public function sendMessage($text)
    {
        if (!empty($text)) {
            return $this->setMessage(new Message($text))->send();
        }

        return false;
    }

    /**
     * Do an HTTP post to Mattermost
     *
     * @return bool
     */
    public function send()
    {
        if (!$this->getMessage()) {
            return false;
        }

        $this->processEnvironment();
        $url     = $this->getWebhook();
        $message = $this->serializeMessage();
        list($httpStatusCode, $response) = $this->sendCurlRequest($url, $message);

        if (400 === $httpStatusCode) {
            $this->cutCurrentMessage();
            $message = $this->serializeMessage();
            list($httpStatusCode, $response) = $this->sendCurlRequest($url, $message);
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

    /**
     * @param $message
     */
    protected function log($message)
    {
        if (!empty($this->logger)) {
            $this->logger->info($message);
        }
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebhook()
    {
        return $this->webhook;
    }

    /**
     * @param string $webhook
     *
     * @return $this
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppname()
    {
        return $this->appname;
    }

    /**
     * @param string $appname
     *
     * @return $this
     */
    public function setAppname($appname)
    {
        $this->appname = $appname;

        return $this;
    }

    /**
     * @return array
     */
    public function getEnvironmentConfigurations()
    {
        return $this->environmentConfigurations;
    }

    /**
     * @param array $environmentConfigurations
     */
    public function setEnvironmentConfigurations($environmentConfigurations)
    {
        $this->environmentConfigurations = $environmentConfigurations;
    }

    /**
     * @return array|null
     */
    public function getEnvironmentConfiguration()
    {
        return array_key_exists($this->getEnvironment(), $this->environmentConfigurations)
            ? $this->environmentConfigurations[ $this->getEnvironment() ]
            : null;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param $conf
     *
     * @return self
     */
    public function setConfiguration($conf)
    {
        $this->configuration = $conf;

        return $this;
    }

    private function processEnvironment()
    {
        $config = $this->getEnvironmentConfiguration();
        if (!empty($config)) {
            $names = ['webhook', 'appname'];
            foreach ($names as $name) {
                if (array_key_exists($name, $config)) {
                    $funcName = "set" . ucfirst($name);
                    $this->$funcName($config[ $name ]);
                }
            }

            $names = ['username', 'channel', 'iconUrl'];
            foreach ($names as $name) {
                if (array_key_exists($name, $config)) {
                    $funcName = 'set' . ucfirst($name);
                    $this->message->$funcName($config[ $name ]);
                }
            }
        }
    }

    /**
     * @param string|null $function
     *
     * @return bool
     */
    public function isEnabled($function = null)
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

    /**
     * @param $url
     * @param $message
     *
     * @return array|null
     */
    private function sendCurlRequest($url, $message)
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
            $text = substr($text, 0, self::MAX_TEXT_LENGTH);
            $this->message->setText($text);
        }
        if (strlen($this->serializeMessage()) > self::MAX_MESSAGE_LENGTH) {
            $attachments = $this->message->getAttachments();
            if (count($attachments) > 1) {
                rewind($attachments);
                $this->message->setAttachments(current($attachments));
            }
            if (strlen($this->serializeMessage()) > self::MAX_MESSAGE_LENGTH) {
                /** @var Attachment $firstAttachment */
                $firstAttachment = current($this->message->getAttachments());
                $firstAttachment->setFallback(substr($firstAttachment->getFallback(), 0, self::CUT_LENGTH));
                $firstAttachment->setTitle(substr($firstAttachment->getTitle(), 0, self::CUT_LENGTH));
                $this->message->setAttachments([$firstAttachment]);
            }
            if (strlen($this->serializeMessage()) > self::MAX_MESSAGE_LENGTH) {
                /** @var Attachment $firstAttachment */
                $firstAttachment = current($this->message->getAttachments());
                $firstAttachment->setFields([]);
                $this->message->setAttachments([$firstAttachment]);
            }
        }
    }
}