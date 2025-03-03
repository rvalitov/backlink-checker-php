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

final class AttributesTest extends TestCase //phpcs:ignore
{
    /**
     * @var BacklinkChecker\SimpleBacklinkChecker
     */
    private BacklinkChecker\SimpleBacklinkChecker $checker;

    public function __construct()
    {
        parent::__construct();
        $this->checker = new BacklinkChecker\SimpleBacklinkChecker();
    }

    public function testLinks(): void
    {
        $checkList = [
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test1$@",
                "target" => "",
                "noFollow" => true,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test2$@",
                "target" => "_blank",
                "noFollow" => false,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test3$@",
                "target" => "_blank",
                "noFollow" => false,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test4$@",
                "target" => "",
                "noFollow" => false,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test5$@",
                "target" => "",
                "noFollow" => false,
            ],
        ];

        foreach ($checkList as $id => $check) {
            $this->assertArrayHasKey("url", $check);
            $this->assertArrayHasKey("pattern", $check);
            $url = $check["url"];
            $pattern = $check["pattern"];
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertNotEmpty($url);
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertNotEmpty($pattern);
            $result = $this->checker->getBacklinks($url, $pattern);
            $response = $result->getResponse();
            $this->assertTrue($response->isSuccess(), "Failed to read webpage $url");
            $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url");
            $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url");

            $backlinks = $result->getBacklinks();
            $this->assertCount(1, $backlinks, "Expected 1 backlinks for $url but got " . count($backlinks));

            foreach ($backlinks as $backlink) {
                $this->assertNotEmpty($backlink->getBacklink(), "Failed to get backlink #$id for $url");
                $this->assertEquals(
                    $check["noFollow"],
                    $backlink->isNoFollow(),
                    "Failed to match 'noFollow' #$id for $url",
                );
                $this->assertEquals(
                    $check["target"],
                    $backlink->getTarget(),
                    "Failed to match 'target' #$id for $url",
                );
            }
        }
    }
}
