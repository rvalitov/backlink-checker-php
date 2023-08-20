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
     * @var string The tag that is used for the backlink, can be "a" or "img"
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
        bool   $noFollow,
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
     */
    public function getAnchor(): string
    {
        return $this->anchor;
    }

    /**
     * @return boolean true if the backlink has "nofollow" tag
     * @deprecated use isNoFollow() instead
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNoFollow(): bool
    {
        return $this->isNoFollow();
    }

    /**
     * @return boolean true if the backlink has "nofollow" tag
     */
    public function isNoFollow(): bool
    {
        return $this->noFollow;
    }

    /**
     * @return string - contents of target attribute of the href
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return string - the tag that is used for the backlink, can be "a" or "img"
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return string - backlink - exact URL that matches the target domain
     */
    public function getBacklink(): string
    {
        return $this->backlink;
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
