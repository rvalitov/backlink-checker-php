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

final class CombinedTest extends TestCase //phpcs:ignore
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
        $this->checkChromeResponse($result, $url, 2);

        // Simple mode should detect only one backlink
        $result = $this->simpleChecker->getBacklinks($url, $pattern, true, false, false);
        $this->checkSimpleResponse($result, $url, 1);
    }

    /**
     * Makes tests on the domain example.com
     * @return void
     */
    public function testExampleDomain()
    {
        $url = "https://example.com";
        $pattern = "@.*@";

        // All modes should detect 1 link
        $result = $this->chromeChecker->getBacklinks($url, $pattern, true, false, false);
        $this->checkChromeResponse($result, $url, 1);

        $result = $this->simpleChecker->getBacklinks($url, $pattern, true, false, false);
        $this->checkSimpleResponse($result, $url, 1);
    }

    /**
     * Checks the response of the Chrome mode
     * @param BacklinkChecker\BacklinkData $data The response data
     * @param string $url The expected URL
     * @param int $expectedCount The expected number of backlinks
     * @return void
     */
    private function checkChromeResponse(BacklinkChecker\BacklinkData $data, string $url, int $expectedCount): void
    {
        $response = $data->getResponse();
        $this->assertTrue($response->isSuccess(), "Failed to read webpage $url");
        $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url");
        $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($url, $response->getUrl());
        $backlinks = $data->getBacklinks();
        $this->assertCount(
            $expectedCount,
            $backlinks,
            "Expected $expectedCount backlinks for $url",
        );
    }

    /**
     * Checks the response of the Simple mode
     * @param BacklinkChecker\BacklinkData $data The response data
     * @param string $url The expected URL
     * @param int $expectedCount The expected number of backlinks
     * @return void
     */
    private function checkSimpleResponse(BacklinkChecker\BacklinkData $data, string $url, int $expectedCount): void
    {
        $backlinks = $data->getBacklinks();
        $this->assertCount(
            $expectedCount,
            $backlinks,
            "Expected $expectedCount backlinks for $url",
        );
    }
}
