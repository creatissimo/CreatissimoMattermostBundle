<?php

namespace Creatissimo\MattermostBundle\Entity;

/**
 * Class Attachment
 * @package Creatissimo\MattermostBundle\Entity
 */
class Attachment
{
    /**
     * @var string $fallback
     */
    private $fallback;

    /**
     * @var string $color
     */
    private $color;

    /**
     * @var string $pretext
     */
    private $pretext;

    /**
     * @var string $title
     */
    private $title;

    /**
     * Attachment constructor.
     */
    private $fields = [];


    /**
     * Attachment constructor.
     *
     * @param string $title
     */
    public function __construct($title)
    {
        $this->setTitle($title);
    }

    /**
     * @return null|string
     */
    public function getFallback(): ?string
    {
        return $this->fallback;
    }


    /**
     * @param $fallback
     *
     * @return $this
     */
    public function setFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }


    /**
     * @param $color
     *
     * @return $this
     */
    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPretext(): ?string
    {
        return $this->pretext;
    }


    /**
     * @param $pretext
     *
     * @return $this
     */
    public function setPretext(string $pretext): self
    {
        $this->pretext = $pretext;

        return $this;
    }

    /**
     * Has pretext
     *
     * @return bool
     */
    public function hasPretext(): bool
    {
        return $this->pretext ? true : false;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Has title
     *
     * @return bool
     */
    public function hasTitle(): bool
    {
        return $this->title ? true : false;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        return $this->fields = $fields;
    }

    /**
     * Add field
     *
     * @param AttachmentField $field
     *
     * @return self
     */
    public function addField(AttachmentField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Has fields
     *
     * @return bool
     */
    public function hasFields(): bool
    {
        return count($this->fields) > 0;
    }
}