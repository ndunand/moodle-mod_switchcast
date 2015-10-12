<?php

/**
 * Class xoctPublicationMetadata
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xoctPublicationMetadata {

    /**
     * @param string $id
     */
    public function __construct($id = '') {
    }

    /**
     * @var string
     */
    public $id = '';
    /**
     * @var string
     */
    public $mediatype;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $flavor;
    /**
     * @var int
     */
    public $size;
    /**
     * @var int
     */
    public $checksum;
    /**
     * @var array
     */
    public $tags;

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getMediatype() {
        return $this->mediatype;
    }

    /**
     * @param string $mediatype
     */
    public function setMediatype($mediatype) {
        $this->mediatype = $mediatype;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getFlavor() {
        return $this->flavor;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor) {
        $this->flavor = $flavor;
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getChecksum() {
        return $this->checksum;
    }

    /**
     * @param int $checksum
     */
    public function setChecksum($checksum) {
        $this->checksum = $checksum;
    }

    /**
     * @return array
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags) {
        $this->tags = $tags;
    }
}