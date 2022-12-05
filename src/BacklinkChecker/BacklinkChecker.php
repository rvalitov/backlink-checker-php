<?php

namespace Valitov\BacklinkChecker;

use InvalidArgumentException;
use RuntimeException;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Class BacklinkChecker
 * Abstract class for checking the backlinks
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov ramilvalitov@gmail.com
 */
abstract class BacklinkChecker
{
    /**
     * @param string $html
     * @param string $pattern
     * @param bool $scanLinks
     * @param bool $scanImages
     * @return Backlink[]
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function getRawBacklink(string $html, string $pattern, bool $scanLinks, bool $scanImages): array
    {
        $result = array();

        if (@preg_match($pattern, null) === false)
            throw new InvalidArgumentException("Invalid pattern. Check the RegExp syntax.");
        if (strlen($html) <= 0 || strlen($pattern) <= 0)
            return $result;
        $dom = HtmlDomParser::str_get_html($html);
        if (empty($dom))
            throw new RuntimeException("Failed to parse HTML");

        if ($scanLinks) {
            //Searching <a> tags
            $list = $dom->find("a[href]");
            if (is_array($list)) {
                foreach ($list as $link) {
                    if (isset($link->href) && preg_match($pattern, $link->href) === 1) {
                        //We found a matching backlink
                        $contents = html_entity_decode(trim($link->plaintext));
                        $target = $link->_target ?? "";
                        $noFollow = false;
                        if (isset($link->rel)) {
                            $relList = explode(" ", $link->rel);
                            if (is_array($relList)) {
                                foreach ($relList as $item) {
                                    if (strtolower(trim($item)) === "nofollow")
                                        $noFollow = true;
                                }
                            }
                        }
                        $result[] = new Backlink($link->href, $contents, $noFollow, $target, "a");
                    }
                }
            }
        }

        if ($scanImages) {
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
        }
        $dom->clear();
        return $result;
    }

    /**
     * @param string $url
     * @param boolean $makeScreenshot
     * @return HttpResponse
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    abstract protected function browsePage(string $url, bool $makeScreenshot): HttpResponse;

    /**
     * @param string $url
     * @param string $pattern
     * @param bool $scanLinks
     * @param bool $scanImages
     * @param boolean $makeScreenshot
     * @return BacklinkData
     */
    public function getBacklinks(string $url, string $pattern, bool $scanLinks = true, bool $scanImages = false, bool $makeScreenshot = false): BacklinkData
    {
        $response = $this->browsePage($url, $makeScreenshot);

        if (!$response->getSuccess())
            $backlinks = [];
        else
            $backlinks = $this->getRawBacklink($response->getResponse(), $pattern, $scanLinks, $scanImages);
        return new BacklinkData($response, $backlinks);
    }
}
