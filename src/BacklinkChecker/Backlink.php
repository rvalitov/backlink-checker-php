<?php

namespace Valitov\BacklinkChecker;

use JsonSerializable;

/**
 * Class Backlink
 * Contains information about a backlink
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov https://github.com/rvalitov
 */
class Backlink implements JsonSerializable
{
    /**
     * @var string Backlink - exact URL that matches the target domain
     */
    protected string $backlink;

    /**
     * @var string Anchor of the link, for example, inner text of <a> tag
     */
    protected string $anchor;

    /**
     * @var bool true if the backlink has "nofollow" tag
     */
    protected bool $noFollow;

    /**
     * @var string Contents of target attribute of the href
     */
    protected string $target;

    /**
     * @var string The tag used for the backlink, can be "a" or "img"
     */
    protected string $tag;

    /**
     * Backlink constructor.
     * @param string $backlink
     * @param string $linkContents
     * @param boolean $noFollow
     * @param string $target
     * @param string $tag
     */
    public function __construct(
        string $backlink,
        string $linkContents,
        bool $noFollow,
        string $target,
        string $tag
    ) {
        $this->backlink = $backlink;
        $this->anchor = $linkContents;
        $this->noFollow = $noFollow;
        $this->target = $target;
        $this->tag = $tag;
    }

    /**
     * @return string - anchor of the link, for example, inner text of <a> tag
     * @psalm-api
     */
    public function getAnchor(): string
    {
        return $this->anchor;
    }

    /**
     * @return boolean true if the backlink has "nofollow" tag
     * @psalm-api
     */
    public function isNoFollow(): bool
    {
        return $this->noFollow;
    }

    /**
     * @return string - contents of target attribute of the href
     * @psalm-api
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return string - the tag used for the backlink, can be "a" or "img"
     * @psalm-api
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return string - backlink - exact URL that matches the target domain
     * @psalm-api
     */
    public function getBacklink(): string
    {
        return $this->backlink;
    }

    /**
     * Function to serialize the object to JSON
     * @return array array representation of the object
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
