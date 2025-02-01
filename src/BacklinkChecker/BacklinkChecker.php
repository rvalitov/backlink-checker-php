<?php

namespace Valitov\BacklinkChecker;

use Exception;
use InvalidArgumentException;
use voku\helper\HtmlDomParser;
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
     */
    protected static function getRawBacklinks(string $html, string $pattern, bool $scanLinks, bool $scanImages): array
    {
        $result = [];
        $isOk = true;

        if (empty($pattern)) {
            throw new InvalidArgumentException("Pattern is empty.");
        }

        try {
            if (preg_match($pattern, "") === false) {
                $isOk = false;
            }
        } catch (Exception) {
            $isOk = false;
        }
        if (!$isOk) {
            throw new InvalidArgumentException("Invalid pattern. Check the RegExp syntax.");
        }
        if (strlen($html) <= 0) {
            return $result;
        }

        $dom = HtmlDomParser::str_get_html($html);

        if ($scanLinks) {
            $result = array_merge($result, self::scanLinks($dom, $pattern));
        }

        if ($scanImages) {
            $result = array_merge($result, self::scanImages($dom, $pattern));
        }
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
     * @param HtmlDomParser $dom - the HTML DOM object
     * @param string $pattern - the pattern (RegExp) to match the backlink URL (href attribute)
     * @return Backlink[] - array of found Backlink objects that match the pattern
     */
    protected static function scanLinks(HtmlDomParser $dom, string $pattern): array
    {
        $result = [];

        if (empty($pattern)) {
            return $result;
        }

        /**
         * Searching <a> tags
         * @psalm-suppress TooManyTemplateParams
         */
        $list = $dom->findMulti("a[href]");

        foreach ($list as $link) {
            $href = trim($link->getAttribute("href"));
            if (!empty($href) && preg_match($pattern, $href) === 1) {
                //We found a matching backlink
                $contents = html_entity_decode(trim($link->text()));
                $target = $link->hasAttribute("target") ? trim($link->getAttribute("target")) : "";
                $noFollow = $link->hasAttribute("rel") && self::isNoFollow($link->getAttribute("rel"));
                $result[] = new Backlink($href, $contents, $noFollow, $target, "a");
            }
        }
        return $result;
    }

    /**
     * Scans the HTML for the backlinks represented by <img> tags
     * @param HtmlDomParser $dom - the HTML DOM object
     * @param string $pattern - the pattern (RegExp) to match the backlink URL (src attribute)
     * @return Backlink[] - array of found Backlink objects that match the pattern
     */
    protected static function scanImages(HtmlDomParser $dom, string $pattern): array
    {
        $result = [];

        if (empty($pattern)) {
            return $result;
        }

        /**
         * Searching <img> tags - image hotlink
         * @psalm-suppress TooManyTemplateParams
         */
        $list = $dom->findMulti("img[src]");

        foreach ($list as $link) {
            $src = trim($link->getAttribute("src"));
            if (!empty($src) && preg_match($pattern, $src) === 1) {
                //We found a matching backlink
                $alt = trim($link->getAttribute("alt"));
                $contents = html_entity_decode(trim($alt));
                $result[] = new Backlink($src, $contents, false, "", "img");
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
     * @psalm-api
     */
    public function getBacklinks(
        string $url,
        string $pattern,
        bool $scanLinks = true,
        bool $scanImages = false,
        bool $makeScreenshot = false,
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
