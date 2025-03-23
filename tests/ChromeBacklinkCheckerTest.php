<?php

//phpcs:ignore
declare(strict_types=1);

require_once __DIR__ . '/Config.php';

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker\ChromeBacklinkChecker;
use Valitov\BacklinkChecker\Backlink;
use Valitov\BacklinkChecker\BacklinkData;
use Valitov\BacklinkChecker\HttpResponse;

// Test-specific subclass to access protected method
class TestChromeBacklinkChecker extends ChromeBacklinkChecker //phpcs:ignore
{
    public function publicBrowsePage(string $url, bool $makeScreenshot): HttpResponse
    {
        return $this->browsePage($url, $makeScreenshot);
    }
}

#[Group('online')]
#[CoversClass(ChromeBacklinkChecker::class)]
#[UsesClass(Backlink::class)]
#[UsesClass(BacklinkData::class)]
#[UsesClass(HttpResponse::class)]
final class ChromeBacklinkCheckerTest extends TestCase //phpcs:ignore
{
    /**
     * @var ChromeBacklinkChecker
     */
    private ChromeBacklinkChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ChromeBacklinkChecker();
    }

    public function testAboutBlankPage(): void
    {
        $backlinks = $this->checker->getBacklinks("about:blank", "@abc@");
        $this->assertEmpty($backlinks->getBacklinks());
    }

    public function testMakeScreenshot(): void
    {
        $checker = new TestChromeBacklinkChecker();
        $response = $checker->publicBrowsePage("https://example.com", true);
        $this->assertNotEmpty($response->getScreenshot());
    }
}
