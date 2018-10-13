<?php

namespace Valitov\BacklinkChecker;
use JsonSerializable;


/**
 * Class Backlink
 * Contains information about a backlink
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov ramilvalitov@gmail.com
 */
class Backlink implements JsonSerializable
{
    /**
     * @var string Backlink - exact URL that matches the target domain
     */
    protected $Backlink;

    /**
     * @var string Anchor of the link, for example, inner text of <a> tag
     */
    protected $Anchor;

    /** @noinspection SpellCheckingInspection */
    /**
     * @var bool true if the backlink has "nofollow" tag
     */
    protected $NoFollow;

    /**
     * @var string Contents of target attribute of the href
     */
    protected $Target;

    /**
     * @var string The tag that is used for the backlink, can be "a" or "img"
     */
    protected $Tag;

    /**
     * Backlink constructor.
     * @param string $Backlink
     * @param string $LinkContents
     * @param boolean $NoFollow
     * @param string $Target
     * @param string $Tag
     */
    public function __construct($Backlink, $LinkContents, $NoFollow, $Target, $Tag)
    {
        $this->Backlink = $Backlink;
        $this->Anchor = $LinkContents;
        $this->NoFollow = $NoFollow;
        $this->Target = $Target;
        $this->Tag = $Tag;
    }

    /**
     * @return string - anchor of the link, for example, inner text of <a> tag
     */
    public function getAnchor()
    {
        return $this->Anchor;
    }

    /**
     * @return boolean true if the backlink has "nofollow" tag
     */
    public function getNoFollow()
    {
        return $this->NoFollow;
    }

    /**
     * @return string - contents of target attribute of the href
     */
    public function getTarget()
    {
        return $this->Target;
    }

    /**
     * @return string - the tag that is used for the backlink, can be "a" or "img"
     */
    public function getTag()
    {
        return $this->Tag;
    }

    /**
     * @return string - backlink - exact URL that matches the target domain
     */
    public function getBacklink()
    {
        return $this->Backlink;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}