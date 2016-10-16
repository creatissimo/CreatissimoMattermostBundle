<?php
/**
 * Mattermost helper
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Creatissimo\MattermostBundle\Service;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class MattermostHelper
{
    /** @var string */
    private $environment;

    /** @var string */
    private $webhook;

    /** @var string */
    private $channel;

    /** @var string */
    private $title;

    /** @var string */
    private $text;

    /** @var string */
    private $name;

    /** @var string */
    private $username;

    /** @var array */
    private $attachments;

    /** @var array */
    private $environmentConfigurations;

    /**
     * @param $environment
     */
    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Format the JSON message to post to Mattermost
     *
     * @param \Exception $exception
     * @return null|string
     */
    protected function buildMessage()
    {
        $json = null;
        $message = array(
            'channel' => $this->getChannel(),
            'text' => $this->getText()
        );
        if(count($this->getAttachments())  > 0 ) {
            $message['attachments'] = $this->attachments();
        }
        if (!empty($this->getUsername())) {
            $message['username'] = $this->getUsername();
        }
        $json = json_encode($message);
        return $json;
    }

    /**
     * Do an HTTP post to Mattermost
     *
     * @param $url
     * @param null $body
     * @return bool
     */
    public function sendMessage()
    {
        if (empty($this->getText()))
        {
            return false;
        }
        $ch = curl_init();
        if (!$ch)
        {
            $this->log('Failed to create curl handle');
            return false;
        }
        $url = $this->getWebhook();
        $message = $this->buildMessage();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($message))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpStatusCode != 200)
        {
            $this->log('Failed to post to mattermost: status ' . $httpStatusCode);
            return false;
        }
        if ($response != 'ok')
        {
            $this->log('Didn\'t get an "ok" back from mattermost, got: ' . $response);
            return false;
        }
        return true;
    }

    protected function log($message)
    {
        if (!empty($this->logger))
        {
            $this->logger->info($message);
        }
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
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $name
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $name
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $name
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @param array $attachment
     */
    public function addttachment($attachment)
    {
        $this->attachments[] = $attachment;
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
}