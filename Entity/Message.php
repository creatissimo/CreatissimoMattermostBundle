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
     * @return null|string
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    /**
     * @param string $iconUrl
     *
     * @return $this
     */
    public function setIconUrl(string $iconUrl): self
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @return array
     */
    public function getFirstAttachment(): array
    {
        return array_values($this->attachments)[0];
    }

    /**
     * @return array
     */
    public function getLastAttachment(): array
    {
        return end($this->attachments);
    }

    /**
     * @param array $attachments
     *
     * @return $this
     */
    public function setAttachments(array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @param Attachment $attachment
     *
     * @return $this
     */
    public function addAttachment(Attachment $attachment): self
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
    public function hasAttachments(): bool
    {
        return count($this->attachments) > 0;
    }
}