<?php

namespace Valitov\BacklinkChecker;

use JsonSerializable;

/**
 * Class BacklinkData
 * Contains information about backlinks
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov ramilvalitov@gmail.com
 */
class BacklinkData implements JsonSerializable
{
    /**
     * @var HttpResponse HTTP response
     */
    protected $response;

    /**
     * @var Backlink[] found backlinks
     */
    protected $backlinks;

    /**
     * BacklinkData constructor.
     * @param HttpResponse $response
     * @param Backlink[] $backlinks
     */
    public function __construct(HttpResponse $response, array $backlinks)
    {
        $this->response = $response;
        $this->backlinks = $backlinks;
    }

    /**
     * @return HttpResponse HTTP response
     */
    public function getResponse(): HttpResponse
    {
        return $this->response;
    }

    /**
     * @return Backlink[] found backlinks
     */
    public function getBacklinks(): array
    {
        return $this->backlinks;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array|null data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): ?array
    {
        return get_object_vars($this);
    }
}
