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

final class CompareModesTest extends TestCase //phpcs:ignore
{
    /**
     * @var BacklinkChecker\ChromeBacklinkChecker
     */
    private BacklinkChecker\ChromeBacklinkChecker $chromeChecker;

    /**
     * @var BacklinkChecker\SimpleBacklinkChecker
     */
    private BacklinkChecker\SimpleBacklinkChecker $simpleChecker;

    public function __construct()
    {
        parent::__construct();
        $this->chromeChecker = new BacklinkChecker\ChromeBacklinkChecker();
        $this->simpleChecker = new BacklinkChecker\SimpleBacklinkChecker();
    }

    /**
     * This method tests that the Chrome mode detects more backlinks than the Simple mode.
     * This happens because the Chrome mode detects dynamic javascript-generated content.
     * @return void
     */
    public function testCompareModes(): void
    {
        $url = Config::TEST_HOST . "js.html";
        $pattern = "@.*@";

        // Chrome mode should detect two backlinks
        $result = $this->chromeChecker->getBacklinks($url, $pattern, true, false, false);
        $response = $result->getResponse();
        $this->assertTrue($response->isSuccess(), "Failed to read webpage $url");
        $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url");
        $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($url, $response->getUrl());
        $backlinks = $result->getBacklinks();
        $backlinksCount = count($backlinks);
        $this->assertCount(
            2,
            $backlinks,
            "Expected $backlinksCount backlinks for $url but got " . count($backlinks),
        );

        // Simple mode should detect only one backlink
        $result = $this->simpleChecker->getBacklinks($url, $pattern, true, false, false);
        $backlinks = $result->getBacklinks();
        $backlinksCount = count($backlinks);
        $this->assertCount(
            1,
            $backlinks,
            "Expected $backlinksCount backlinks for $url but got " . count($backlinks),
        );
    }
}
