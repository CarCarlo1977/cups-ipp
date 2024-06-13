<?php

namespace Smalot\Cups\Model;

use finfo;
use function GuzzleHttp\Psr7\mimetype_from_filename;

/**
 * Class Job
 *
 * @package Smalot\Cups\Model
 */
class Job implements JobInterface
{
    use Traits\AttributeAware;
    use Traits\UriAware;

    const CONTENT_FILE = 'file';
    const CONTENT_TEXT = 'text';
    const SIDES_TWO_SIDED_LONG_EDGE = 'two-sided-long-edge';
    const SIDES_TWO_SIDED_SHORT_EDGE = 'two-sided-short-edge';
    const SIDES_ONE_SIDED = 'one-sided';

    protected $id;
    protected $printerUri;
    protected $name;
    protected $username;
    protected $pageRanges;
    protected $copies;
    protected $sides;
    protected $fidelity;
    protected $content = [];
    protected $state;
    protected $stateReason;

    public function __construct()
    {
        $this->copies = 1;
        $this->sides = self::SIDES_ONE_SIDED;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getPrinterUri()
    {
        return $this->printerUri;
    }

    public function setPrinterUri($printerUri)
    {
        $this->printerUri = $printerUri;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPageRanges()
    {
        return $this->pageRanges;
    }

    public function setPageRanges($pageRanges)
    {
        $this->pageRanges = $pageRanges;
        return $this;
    }

    public function getCopies()
    {
        return $this->copies;
    }

    public function setCopies($copies)
    {
        $this->copies = $copies;
        return $this;
    }

    public function getSides()
    {
        return ($this->sides ?: self::SIDES_ONE_SIDED);
    }

    public function setSides($sides)
    {
        $this->sides = $sides;
        return $this;
    }

    public function getFidelity()
    {
        return $this->fidelity;
    }

    public function setFidelity($fidelity)
    {
        $this->fidelity = $fidelity;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function addFile($filename, $name = '', $mimeType = null)
    {
        if (empty($name)) {
            $name = basename($filename);
        }

        if ($mimeType === null) {
            $mimeType = mimetype_from_filename($filename);
        }

        return $this->addBinary(fopen($filename, 'r'), $name, $mimeType);
    }

    /**
     * @param resource $stream
     * @param string $name
     * @param string $mimeType
     *
     * @return Job
     */
    public function addBinary($stream, $name, $mimeType = null)
    {
        if ($mimeType === null && class_exists(finfo::class)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer(stream_get_contents($stream));
            rewind($stream); // Reset the stream pointer to the beginning
        }

        $mimeType = is_string($mimeType) ? $mimeType : 'application/octet-stream';

        $this->content[] = [
            'type' => self::CONTENT_FILE,
            'name' => $name,
            'mimeType' => $mimeType,
            'binary' => stream_get_contents($stream),
        ];

        return $this;
    }

    public function addText($text, $name = '', $mimeType = 'text/plain')
    {
        $this->content[] = [
            'type' => self::CONTENT_TEXT,
            'name' => $name,
            'mimeType' => $mimeType,
            'text' => $text,
        ];

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function getStateReason()
    {
        return $this->stateReason;
    }

    public function setStateReason($stateReason)
    {
        $this->stateReason = $stateReason;
        return $this;
    }
}
