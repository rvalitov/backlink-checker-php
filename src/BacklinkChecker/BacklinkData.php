<?php

namespace Valitov\BacklinkChecker;

use JsonSerializable;

/**
 * Class BacklinkData
 * Contains information about backlinks
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov https://github.com/rvalitov
 */
class BacklinkData implements JsonSerializable
{
    /**
     * @var HttpResponse HTTP response
     */
    protected HttpResponse $response;

    /**
     * @var Backlink[] found backlinks
     */
    protected array $backlinks;

    /**
     * BacklinkData constructor.
     * @param HttpResponse $response HTTP response
     * @param Backlink[] $backlinks found backlinks
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
     * Function to serialize the object to JSON
     * @return array<mixed> array representation of the object
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): ?array
    {
        return get_object_vars($this);
    }
}
