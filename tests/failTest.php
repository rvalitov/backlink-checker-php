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

final class failTest extends TestCase
{
    /**
     * @var BacklinkChecker\SimpleBacklinkChecker
     */
    private $checker;

    const TEST_HOST = "http://localhost:8080/";

    public function __construct()
    {
        parent::__construct();
        $this->checker = new BacklinkChecker\SimpleBacklinkChecker();
    }

    public function testBadRegexp()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->checker->getBacklinks(self::TEST_HOST . "simple.html", "abc", true, false, false);
    }

    public function testEmptyHtml()
    {
        $result = $this->checker->getBacklinks(self::TEST_HOST . "empty.html", "@abc@", true, false, false);
        $response = $result->getResponse();
        $this->assertTrue($response->getSuccess());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($result->getBacklinks());
    }

    public function testNotFoundHtml()
    {
        $result = $this->checker->getBacklinks(self::TEST_HOST . "404.html", "@abc@", true, false, false);
        $response = $result->getResponse();
        $this->assertFalse($response->getSuccess());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEmpty($result->getBacklinks());
    }

    public function testInvalidProtocol()
    {
        $this->expectException(RuntimeException::class);
        $this->checker->getBacklinks("ppp://localhost:8080/404.html", "@abc@", true, false, false);
    }
}
