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

    public function __construct()
    {
        parent::__construct();
        $this->checker = new BacklinkChecker\SimpleBacklinkChecker();
    }

    public function testBadRegexp()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->checker->getBacklinks("http://localhost/simple.html", "abc", true, false, false);
    }

    public function testEmptyHtml()
    {
        $result = $this->checker->getBacklinks("http://localhost/empty.html", "@abc@", true, false, false);
        $response = $result->getResponse();
        $this->assertTrue($response->getSuccess());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($result->getBacklinks());
    }

    public function testNotFoundHtml()
    {
        $result = $this->checker->getBacklinks("http://localhost/404.html", "@abc@", true, false, false);
        $response = $result->getResponse();
        $this->assertFalse($response->getSuccess());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEmpty($result->getBacklinks());
    }

    public function testInvalidProtocol()
    {
        $this->expectException(\RuntimeException::class);
        $this->checker->getBacklinks("ppp://localhost/404.html", "@abc@", true, false, false);
    }
}
