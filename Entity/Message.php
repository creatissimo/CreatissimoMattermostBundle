<?php

namespace Creatissimo\MattermostBundle\Entity;

/**
 * Class Message
 * @package Creatissimo\MattermostBundle\Entity
 */
class Message
{
    private ?string $channel     = null;
    private string  $text;
    private array   $attachments = [];

    public function __construct(string $text)
    {
        $this->setText($text);
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getFirstAttachment(): array
    {
        return array_values($this->attachments)[0];
    }

    public function getLastAttachment(): array
    {
        return end($this->attachments);
    }

    public function setAttachments(array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if ($attachment->hasTitle() || $attachment->hasPretext() || $attachment->hasFields()) {
            $this->attachments[] = $attachment;
        }

        return $this;
    }

    public function hasAttachments(): bool
    {
        return count($this->attachments) > 0;
    }
}
