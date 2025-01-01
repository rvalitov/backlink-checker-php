<?php

namespace Valitov\BacklinkChecker;

use Exception;
use InvalidArgumentException;
use KubAT\PhpSimple\HtmlDomParser;
use simple_html_dom\simple_html_dom;
use UnexpectedValueException;

/**
 * Class BacklinkChecker
 * Abstract class for checking the backlinks
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov https://github.com/rvalitov
 */
abstract class BacklinkChecker
{
    /**
     * Searches for backlinks in the HTML using the specified pattern
     * @param string $html - the HTML content
     * @param string $pattern - the pattern (RegExp) to match the backlink URL
     * @param bool $scanLinks - if true, the <a> tags will be scanned
     * @param bool $scanImages - if true, the <img> tags will be scanned
     * @return Backlink[] - array of found Backlink objects that match the pattern
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    protected static function getRawBacklinks(string $html, string $pattern, bool $scanLinks, bool $scanImages): array
    {
        $result = [];
        $isOk = true;

        try {
            if (preg_match($pattern, null) === false) {
                $isOk = false;
            }
        } catch (Exception) {
            $isOk = false;
        }
        if (!$isOk) {
            throw new InvalidArgumentException("Invalid pattern. Check the RegExp syntax.");
        }
        if (strlen($html) <= 0 || strlen($pattern) <= 0) {
            return $result;
        }
        $dom = HtmlDomParser::str_get_html($html);
        if (empty($dom)) {
            throw new UnexpectedValueException("Failed to parse HTML");
        }

        if ($scanLinks) {
            $result = array_merge($result, self::scanLinks($dom, $pattern));
        }

        if ($scanImages) {
            $result = array_merge($result, self::scanImages($dom, $pattern));
        }
        $dom->clear();
        return $result;
    }

    /**
     * Checks if the rel attribute contains "nofollow"
     * @param string $rel - value of the rel attribute
     * @return bool - true if the rel attribute contains "nofollow"
     */
    protected static function isNoFollow(string $rel): bool
    {
        $relList = explode(" ", $rel);
        if (!is_array($relList)) {
            return false;
        }

        $noFollow = false;
        foreach ($relList as $item) {
            if (strtolower(trim($item)) === "nofollow") {
                $noFollow = true;
            }
        }

        return $noFollow;
    }

    /**
     * Scans the HTML for the backlinks represented by <a> tags
     * @param simple_html_dom $dom - the HTML DOM object
     * @param string $pattern - the pattern (RegExp) to match the backlink URL (href attribute)
     * @return Backlink[] - array of found Backlink objects that match the pattern
     */
    protected static function scanLinks(simple_html_dom $dom, string $pattern): array
    {
        $result = [];

        //Searching <a> tags
        $list = $dom->find("a[href]");
        if (!is_array($list)) {
            return $result;
        }

        foreach ($list as $link) {
            if (isset($link->href) && preg_match($pattern, $link->href) === 1) {
                //We found a matching backlink
                $contents = html_entity_decode(trim($link->plaintext));
                $target = $link->target ?? "";
                $noFollow = self::isNoFollow($link->rel);
                $result[] = new Backlink($link->href, $contents, $noFollow, $target, "a");
            }
        }
        return $result;
    }

    /**
     * Scans the HTML for the backlinks represented by <img> tags
     * @param simple_html_dom $dom - the HTML DOM object
     * @param string $pattern - the pattern (RegExp) to match the backlink URL (src attribute)
     * @return Backlink[] - array of found Backlink objects that match the pattern
     */
    protected static function scanImages(simple_html_dom $dom, string $pattern): array
    {
        $result = [];

        //Searching <img> tags - image hotlink
        $list = $dom->find("img[src]");
        if (is_array($list)) {
            foreach ($list as $link) {
                if (isset($link->src) && preg_match($pattern, $link->src) === 1) {
                    //We found a matching backlink
                    $contents = isset($link->alt) ? html_entity_decode(trim($link->alt)) : "";
                    $result[] = new Backlink($link->src, $contents, false, "", "img");
                }
            }
        }
        return $result;
    }

    /**
     * Retrieves the HTML content of the page and optionally makes a screenshot
     * @param string $url - the URL of the page
     * @param boolean $makeScreenshot - if true, the screenshot will be made
     * @return HttpResponse - the response object
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    abstract protected function browsePage(string $url, bool $makeScreenshot): HttpResponse;

    /**
     * Retrieves the backlinks from the specified URL
     * @param string $url - the URL of the page
     * @param string $pattern - the pattern (RegExp) to match the backlink URL
     * @param bool $scanLinks - if true, the <a> tags will be scanned
     * @param bool $scanImages - if true, the <img> tags will be scanned
     * @param boolean $makeScreenshot - if true, the screenshot will be made
     * @return BacklinkData - the object containing the response and the backlinks
     */
    public function getBacklinks(
        string $url,
        string $pattern,
        bool   $scanLinks = true,
        bool   $scanImages = false,
        bool   $makeScreenshot = false,
    ): BacklinkData {
        $response = $this->browsePage($url, $makeScreenshot);

        if (!$response->isSuccess()) {
            $backlinks = [];
        } else {
            $backlinks = self::getRawBacklinks($response->getResponse(), $pattern, $scanLinks, $scanImages);
        }
        return new BacklinkData($response, $backlinks);
    }
}
