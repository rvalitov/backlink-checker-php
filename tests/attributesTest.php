<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/BacklinkChecker/Backlink.php';
require_once __DIR__ . '/../src/BacklinkChecker/BacklinkData.php';
require_once __DIR__ . '/../src/BacklinkChecker/BacklinkChecker.php';
require_once __DIR__ . '/../src/BacklinkChecker/HttpResponse.php';
require_once __DIR__ . '/../src/BacklinkChecker/SimpleBacklinkChecker.php';
require_once __DIR__ . '/../src/BacklinkChecker/ChromeBacklinkChecker.php';

use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker;

final class attributesTest extends TestCase
{
    /**
     * @var BacklinkChecker\SimpleBacklinkChecker
     */
    private $checker;

    public function __construct()
    {
        parent::__construct();
        $this->checker = new BacklinkChecker\SimpleBacklinkChecker();
    }

    public function testLinks()
    {
        $check_list = [
            [
                "url" => "http://localhost/follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com$@",
                "target" => "",
                "noFollow" => true,
            ],
            [
                "url" => "http://localhost/follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/new$@",
                "target" => "_blank",
                "noFollow" => false,
            ],
        ];

        $this->assertNotEmpty($check_list);

        foreach ($check_list as $check) {
            $url = $check["url"];
            $pattern = $check["pattern"];
            $this->assertNotEmpty($url);
            $this->assertNotEmpty($pattern);
            $result = $this->checker->getBacklinks($url, $pattern, true, false, false);
            $response = $result->getResponse();
            $this->assertTrue($response->getSuccess(), "Failed to read webpage $url");
            $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url");
            $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url");

            $backlinks = $result->getBacklinks();
            $this->assertCount(1, $backlinks, "Expected 1 backlinks for $url but got " . count($backlinks));

            foreach ($backlinks as $id => $backlink) {
                $this->assertNotEmpty($backlink->getBacklink(), "Failed to get backlink $id for $url");
                $this->assertEquals($check["noFollow"], $backlink->getNoFollow());
                $this->assertEquals($check["target"], $backlink->getTarget());
            }
        }
    }
}
