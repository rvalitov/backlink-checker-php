<?php

//phpcs:ignore
declare(strict_types=1);
require_once __DIR__ . '/../src/BacklinkChecker/Backlink.php';
require_once __DIR__ . '/../src/BacklinkChecker/BacklinkData.php';
require_once __DIR__ . '/../src/BacklinkChecker/BacklinkChecker.php';
require_once __DIR__ . '/../src/BacklinkChecker/HttpResponse.php';
require_once __DIR__ . '/../src/BacklinkChecker/SimpleBacklinkChecker.php';
require_once __DIR__ . '/../src/BacklinkChecker/ChromeBacklinkChecker.php';
require_once __DIR__ . '/Config.php';

use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker;

final class ChromeModeTest extends TestCase //phpcs:ignore
{
    /**
     * @var BacklinkChecker\ChromeBacklinkChecker
     */
    private BacklinkChecker\ChromeBacklinkChecker $checker;

    public const URL_LIST = [
        [
            "url" => Config::TEST_HOST . "noLinks.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 0,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "simple.html",
            "pattern" => "@^https://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "simple.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 2,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "emptyAnchor.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "images.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => true,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "images.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 0,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "images.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => false,
            "scanImages" => true,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "noLinks.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 0,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->checker = new BacklinkChecker\ChromeBacklinkChecker();
    }

    public function testLinks()
    {
        $this->assertNotEmpty(self::URL_LIST);

        $properties = [
            "backlink",
            "anchor",
            "noFollow",
            "target",
            "tag",
        ];

        foreach (self::URL_LIST as $check) {
            $url = $check["url"];
            $pattern = $check["pattern"];
            $backlinksCount = $check["backlinks"];
            $this->assertNotEmpty($url);
            $this->assertNotEmpty($pattern);
            $this->assertGreaterThanOrEqual(0, $backlinksCount);
            $result = $this->checker->getBacklinks($url, $pattern, $check["scanLinks"], $check["scanImages"], true);
            $response = $result->getResponse();
            $this->assertTrue($response->isSuccess(), "Failed to read webpage $url");
            $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url");
            $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url");
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($url, $response->getUrl());

            $json = $result->jsonSerialize();
            $this->assertNotEmpty($json, "Failed to get jsonSerialize for $url");
            foreach (
                [
                "backlinks",
                "response",
                ] as $property
            ) {
                $this->assertArrayHasKey($property, $json, "Serialize for $url must contain '$property' property");
            }
            $backlinks = $result->getBacklinks();
            $this->assertCount(
                $backlinksCount,
                $backlinks,
                "Expected $backlinksCount backlinks for $url but got " . count($backlinks),
            );
            $this->assertNotEmpty($response->getScreenshot());
            if ($backlinksCount > 0) {
                foreach ($backlinks as $id => $backlink) {
                    $this->assertNotEmpty($backlink->getBacklink(), "Failed to get backlink $id for $url");
                    if (!$check["emptyAnchor"]) {
                        $this->assertNotEmpty($backlink->getAnchor(), "Failed to get anchor $id for $url");
                    } else {
                        $this->assertEmpty($backlink->getAnchor(), "Failed to get empty anchor $id for $url");
                    }

                    $this->assertNotEmpty($backlink->getTag(), "Failed to get tag $id for $url");
                    $array = $backlink->jsonSerialize();
                    $this->assertNotEmpty($array, "Failed to get jsonSerialize for $url");

                    foreach ($properties as $property) {
                        $this->assertArrayHasKey(
                            $property,
                            $array,
                            "Serialize for $url must contain '$property' property",
                        );
                    }
                }
            }
        }
    }

    public function testAboutBlank()
    {
        $backlinks = $this->checker->getBacklinks("about:blank", "@abc@");
        $this->assertNotEmpty($backlinks);
        $this->assertEmpty($backlinks->getBacklinks());
    }

    public function testEmptyURL()
    {
        $this->expectException(Nesk\Rialto\Exceptions\Node\FatalException::class);
        $this->checker->getBacklinks("", "@abc@");
    }

    public function testMissingURL()
    {
        $backlinks = $this->checker->getBacklinks(Config::TEST_HOST . "missing", "@abc@");
        $this->assertNotEmpty($backlinks);
        $this->assertEmpty($backlinks->getBacklinks());
    }
}
