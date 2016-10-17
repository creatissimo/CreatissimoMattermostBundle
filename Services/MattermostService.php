<?php
/**
 * Mattermost service
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Creatissimo\MattermostBundle\Services;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

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

    /** @var string */
    private $icon;

    /** @var string */
    private $username;

    /** @var string */
    private $channel;

    /** @var string */
    private $text;

    /** @var array */
    private $attachments;

    /** @var array */
    private $environmentConfigurations;

    /**
     * @param $environment
     * @param LoggerInterface $looger
     */
    public function __construct($environment, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->setEnvironment($environment);
        $this->processEnvironment();
    }

    /**
     * @param String $text
     * @param String|null $channel
     * @param String|null $username
     * @param String|null $icon
     */
    public function setMessage($text, $channel=NULL, $username=NULL, $icon=NULL)
    {
        $this->setText($text);
        if($channel) $this->setChannel($channel);
        if($username) $this->setUsername($username);
        if($icon) $this->setIcon($icon);
    }

    /**
     * Format the JSON message to post to Mattermost
     *
     * @return null|string
     */
    protected function buildMessage()
    {
        if (empty($this->getText())) return false;

        $json = null;
        $message = [ 'text' => $this->getText() ];

        if(!empty($this->getChannel())) {
            $message[ 'channel'] = $this->getChannel();
        }

        if (!empty($this->getIcon())) {
            $message['icon_url'] = $this->getIcon();
        }

        if (!empty($this->getUsername())) {
            $message['username'] = $this->getUsername();
        }

        if (!empty($this->getAttachments())) {
            $message['attachments'] = $this->getAttachments();
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
        if (empty($this->getText())) {
            return false;
        }
        $ch = curl_init();
        if (!$ch) {
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
        if ($httpStatusCode != 200) {
            $this->log('Failed to post to mattermost: status ' . $httpStatusCode . '; Message: ' . $message .' (' . $response . ')');
            return false;
        } elseif ($response != 'ok') {
            $this->log('Didn\'t get an "ok" back from mattermost, got: ' . $response);
            return false;
        }
        return true;
    }

    protected function processEnvironment()
    {
        $environmentConf = $this->getEnvironmentConfiguration();
        if($environmentConf && is_array($environmentConf)) {
            if (array_key_exists('webhook', $environmentConf)) $this->setWebhook($environmentConf['webhook']);
            if (array_key_exists('appname', $environmentConf)) $this->setAppname($environmentConf['appname']);
            if (array_key_exists('botname', $environmentConf)) $this->setUsername($environmentConf['botname']);
            if (array_key_exists('icon', $environmentConf)) $this->setIcon($environmentConf['icon']);
            if (array_key_exists('channel', $environmentConf)) $this->setChannel($environmentConf['channel']);
        }
    }

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
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
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
    public function getAppname()
    {
        return $this->appname;
    }

    /**
     * @param string $appname
     */
    public function setAppname($appname)
    {
        $this->appname = $appname;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
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

    /**
     * @return array
     */
    public function getEnvironmentConfiguration()
    {
        return $this->environmentConfigurations[$this->getEnvironment()];
    }
}