<?php

//phpcs:ignore
declare(strict_types=1);
require_once __DIR__ . '/../src/BacklinkChecker/Backlink.php';
require_once __DIR__ . '/../src/BacklinkChecker/BacklinkData.php';
require_once __DIR__ . '/../src/BacklinkChecker/BacklinkChecker.php';
require_once __DIR__ . '/../src/BacklinkChecker/HttpResponse.php';
require_once __DIR__ . '/../src/BacklinkChecker/SimpleBacklinkChecker.php';
require_once __DIR__ . '/../src/BacklinkChecker/ChromeBacklinkChecker.php';
require_once __DIR__ . '/config.php';

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

    public function testLinks()
    {
        $checkList = [
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com$@",
                "target" => "",
                "noFollow" => true,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/new$@",
                "target" => "_blank",
                "noFollow" => false,
            ],
        ];

        $this->assertNotEmpty($checkList);

        foreach ($checkList as $check) {
            $url = $check["url"];
            $pattern = $check["pattern"];
            $this->assertNotEmpty($url);
            $this->assertNotEmpty($pattern);
            $result = $this->checker->getBacklinks($url, $pattern);
            $response = $result->getResponse();
            $this->assertTrue($response->isSuccess(), "Failed to read webpage $url");
            $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url");
            $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url");

            $backlinks = $result->getBacklinks();
            $this->assertCount(1, $backlinks, "Expected 1 backlinks for $url but got " . count($backlinks));

            foreach ($backlinks as $id => $backlink) {
                $this->assertNotEmpty($backlink->getBacklink(), "Failed to get backlink $id for $url");
                $this->assertEquals($check["noFollow"], $backlink->isNoFollow());
                $this->assertEquals($check["target"], $backlink->getTarget());
            }
        }
    }
}
