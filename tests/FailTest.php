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

final class FailTest extends TestCase //phpcs:ignore
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

    public function testBadRegexp()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->checker->getBacklinks(Config::TEST_HOST . "simple.html", "abc");
    }

    public function testEmptyHtml()
    {
        $result = $this->checker->getBacklinks(Config::TEST_HOST . "empty.html", "@abc@");
        $response = $result->getResponse();
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($result->getBacklinks());
    }

    public function testNotFoundHtml()
    {
        $result = $this->checker->getBacklinks(Config::TEST_HOST . "404.html", "@abc@");
        $response = $result->getResponse();
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEmpty($result->getBacklinks());
    }

    public function testInvalidProtocol()
    {
        $this->expectException(RuntimeException::class);
        $this->checker->getBacklinks("ppp://localhost:8080/404.html", "@abc@");
    }
}
