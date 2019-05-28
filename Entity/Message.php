<?php

namespace Creatissimo\MattermostBundle\Entity;

/**
 * Class Message
 * @package Creatissimo\MattermostBundle\Entity
 */
class Message
{
    /** @var string */
    private $channel;

    /** @var string */
    private $username;

    /** @var string */
    private $iconUrl;

    /** @var string */
    private $text;

    /** @var array */
    private $attachments = [];

    /**
     * Message constructor.
     *
     * @param $text
     */
    public function __construct($text)
    {
        $this->setText($text);
    }


    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
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
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        return $this->iconUrl;
    }

    /**
     * @param string $iconUrl
     *
     * @return $this
     */
    public function setIconUrl($iconUrl)
    {
        $this->iconUrl = $iconUrl;

        return $this;
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
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return array
     */
    public function getFirstAttachment()
    {
        return array_values($this->attachments)[0];
    }

    /**
     * @return array
     */
    public function getLastAttachment()
    {
        return end($this->attachments);
    }

    /**
     * @param array $attachments
     *
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @param Attachment $attachment
     *
     * @return $this
     */
    public function addAttachment(Attachment $attachment)
    {
        if ($attachment->hasTitle() || $attachment->hasPretext() || $attachment->hasFields()) {
            $this->attachments[] = $attachment;
        }

        return $this;
    }

    /**
     * Has attachments
     *
     * @return bool
     */
    public function hasAttachments()
    {
        return count($this->attachments) > 0;
    }
}