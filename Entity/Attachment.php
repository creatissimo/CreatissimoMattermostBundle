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
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }


    /**
     * @param $fallback
     *
     * @return $this
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }


    /**
     * @param $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getPretext()
    {
        return $this->pretext;
    }


    /**
     * @param $pretext
     *
     * @return $this
     */
    public function setPretext($pretext)
    {
        $this->pretext = $pretext;

        return $this;
    }

    /**
     * Has pretext
     *
     * @return bool
     */
    public function hasPretext()
    {
        return $this->pretext ? true : false;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Has title
     *
     * @return bool
     */
    public function hasTitle()
    {
        return $this->title ? true : false;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Add field
     *
     * @param AttachmentField $field
     *
     * @return self
     */
    public function addField(AttachmentField $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Has fields
     *
     * @return bool
     */
    public function hasFields()
    {
        return count($this->fields) > 0;
    }
}