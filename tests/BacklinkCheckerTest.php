<?php

//phpcs:ignore
declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker\Backlink;
use Valitov\BacklinkChecker\BacklinkChecker;
use Valitov\BacklinkChecker\BacklinkData;
use Valitov\BacklinkChecker\HttpResponse;
use voku\helper\HtmlDomParser;

// Test-specific subclass to access protected methods
class TestBacklinkChecker extends BacklinkChecker //phpcs:ignore
{
    public function publicGetRawBacklinks(string $html, string $pattern, bool $scanLinks, bool $scanImages): array
    {
        return self::getRawBacklinks($html, $pattern, $scanLinks, $scanImages);
    }

    public function publicIsNoFollow(string $rel): bool
    {
        return self::isNoFollow($rel);
    }

    public function publicScanLinks(HtmlDomParser $dom, string $pattern): array
    {
        return self::scanLinks($dom, $pattern);
    }

    public function publicScanImages(HtmlDomParser $dom, string $pattern): array
    {
        return self::scanImages($dom, $pattern);
    }

    // Implement abstract method (not used in these tests but required)
    protected function browsePage(string $url, bool $makeScreenshot): HttpResponse
    {
        return new HttpResponse($url, 200, [], '', true, $makeScreenshot ? 'test' : '');
    }
}

#[Group('offline')]
#[CoversClass(BacklinkChecker::class)]
#[UsesClass(Backlink::class)]
#[UsesClass(BacklinkData::class)]
#[UsesClass(HttpResponse::class)]
class BacklinkCheckerTest extends TestCase //phpcs:ignore
{
    private TestBacklinkChecker $checker;

    public function setUp(): void
    {
        $this->checker = $this->getMockBuilder(TestBacklinkChecker::class)
            ->onlyMethods(['browsePage'])
            ->getMock();
    }

    public function testGetRawBacklinksTags(): void
    {
        $html = '<a href="https://example.com/test">Test Link</a>';
        $pattern = '/example\.com/';

        $backlinks = $this->checker->publicGetRawBacklinks($html, $pattern, true, false);

        $this->assertCount(1, $backlinks);
        $this->assertInstanceOf(Backlink::class, $backlinks[0]);
        $this->assertEquals('https://example.com/test', $backlinks[0]->getBacklink());
        $this->assertEquals('Test Link', $backlinks[0]->getAnchor());
        $this->assertEquals('a', $backlinks[0]->getTag());

        $html = '<img src="https://example.com/image.jpg" alt="Test Image">';
        $backlinks = $this->checker->publicGetRawBacklinks($html, $pattern, false, true);

        $this->assertCount(1, $backlinks);
        $this->assertInstanceOf(Backlink::class, $backlinks[0]);
        $this->assertEquals('https://example.com/image.jpg', $backlinks[0]->getBacklink());
        $this->assertEquals('Test Image', $backlinks[0]->getAnchor());
        $this->assertEquals('img', $backlinks[0]->getTag());
    }

    /**
     * Test the protected method getRawBacklinks with simple regular expressions
     * @return void
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     */
    public function testGetRawBacklinksPatternTests(): void
    {
        // Test with invalid regex
        $invalidRegex = [
            // Empty pattern
            "",
            // Missing closing bracket
            "/[a-z",
            // Unescaped special character
            "/hello(world/",
            // Unbalanced parentheses
            "/(abc/",
            // Invalid range in character class
            "/[z-a]/",
            // Dangling metacharacter
            "/*abc/",
        ];

        foreach ($invalidRegex as $regex) {
            $exceptionFired = false;
            try {
                $this->checker->publicGetRawBacklinks(
                    "<a href='https://example.com'>Example</a>",
                    $regex,
                    true,
                    false,
                );
            } catch (\Exception $ex) {
                $exceptionFired = true;
            }
            $this->assertTrue($exceptionFired, "Failed to match invalid regex: $regex");
        }

        // Test with valid regex, syntax:
        // 1st line: regex
        // 2nd line: HTML that provides a match for the regex
        // 3rd line: HTML that provides a zero match for the regex
        $validRegex = [
            [
                // Match any URL
                "/https?:\/\/[^\s]+/",
                "<a href='https://example.com'>Example</a>",
            ],
            [
                // Match specific domain
                "/https?:\/\/(www\.)?example\.com/",
                "<a href='https://example.com'>Example</a>",
                "<a href='https://www2.example.com'>Example</a>"
            ],
            [
                // Match URLs with query parameters
                "/https?:\/\/[^\s]+\?[^\s]+/",
                "<a href='https://example.com/test?query'>Example</a>",
                "<a href='https://example.com/test'>Example</a>"
            ],
            [
                // Match URLs with fragments
                "/https?:\/\/[^\s]+#[^\s]+/",
                "<a href='https://example.com#help'>Example</a>",
                "<a href='https://example.com'>Example</a>"
            ],
            [
                // Match relative URLs
                "/^\/[^\s]+/",
                "<a href='/example'>Example</a>",
                "<a href='https://example.com'>Example</a>"
            ]
        ];

        foreach ($validRegex as $id => $item) {
            $regex = $item[0];
            $htmlMatch = $item[1];
            $htmlNoMatch = $item[2] ?? '';
            $result = $this->checker->publicGetRawBacklinks(
                $htmlMatch,
                $regex,
                true,
                false,
            );
            $this->assertNotEmpty($result, "Failed to find a match for #$id\r\nRegex: $regex\r\nHTML: $htmlMatch");
            if (!empty($htmlNoMatch)) {
                $result = $this->checker->publicGetRawBacklinks(
                    $htmlNoMatch,
                    $regex,
                    true,
                    false,
                );
                $this->assertEmpty($result, "Match must not be detected for #$id\r\nRegex: $regex\r\nHTML: $htmlNoMatch");
            }
        }
    }

    /**
     * Test the protected method getRawBacklinks with HTML expressions
     * @return void
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     */
    public function testGetRawBacklinksWithDifferentLinks(): void
    {
        // Test with different HTML content
        $htmlContents = [
            // Basic valid link
            "<a href='https://example.com'>Example</a>",
            // Link with additional attributes
            "<a href='https://example.com' title='Example Title' target='_blank'>Example</a>",
            // Link with no href attribute
            "<a title='No Href'>No Href</a>",
            // Link with empty href attribute
            "<a href=''>Empty Href</a>",
            // Link with relative URL
            "<a href='/relative/path'>Relative Path</a>",
            // Link with JavaScript in href
            "<a href='javascript:void(0);'>JavaScript Link</a>",
            // Link with special characters in URL
            "<a href='https://example.com/?q=search&lang=en'>Special Characters</a>",
            // Link with encoded characters in URL
            "<a href='https://example.com/%20space'>Encoded Space</a>",
            // Link with nested tags inside
            "<a href='https://example.com'><span>Nested Tag</span></a>",
            // Link with broken HTML
            "<a href='https://example.com'>Broken Link",
            // Link with multiple href attributes (invalid HTML)
            "<a href='https://example1.com' href='https://example2.com'>Multiple Hrefs</a>",
            // Link with href attribute containing spaces
            "<a href=' https://example.com '>Href with Spaces</a>",
            // Link with href attribute containing newlines
            "<a href='\nhttps://example.com\n'>Href with Newlines</a>",
            // Link with href attribute containing tabs
            "<a href='\thttps://example.com\t'>Href with Tabs</a>",
            // Link with data URI
            "<a href='data:text/plain;base64,SGVsbG8sIFdvcmxkIQ=='>Data URI</a>",
            // Link with a mailto scheme
            "<a href='mailto:example@example.com'>Email Link</a>",
            // Link with a tel scheme
            "<a href='tel:+1234567890'>Telephone Link</a>",
            // Link with fragment identifier
            "<a href='#section1'>Fragment Identifier</a>",
            // Link with query parameters
            "<a href='https://example.com/?param1=value1&param2=value2'>Query Parameters</a>",
        ];

        $regex = "/.+/";
        foreach ($htmlContents as $html) {
            $result = $this->checker->publicGetRawBacklinks(
                $html,
                $regex,
                true,
                false,
            );
            if (mb_strpos($html, "No Href") === false && mb_strpos($html, "Empty Href") === false) {
                $this->assertNotEmpty($result, "Failed to match valid regex: $regex with HTML: $html");
                $this->assertEquals(1, count($result), "Expected 1 backlink but got " . count($result));
                $anchor = $result[0]->getAnchor();
                $this->assertNotEmpty($anchor, "Failed to get anchor from backlink: $html");
                $this->assertStringContainsString($anchor, $html, "Anchor \"$anchor\" does not match the HTML content: $html");
                $backlink = $result[0]->getBacklink();
                if (!empty($backlink)) {
                    $this->assertStringContainsString($backlink, $html, "Backlink \"$backlink\" does not match the HTML content: $html");
                }
            } else {
                $this->assertEmpty($result, "Failed to match valid regex: $regex with HTML: $html");
            }
        }
    }

    public function testIsNoFollow(): void
    {
        $this->assertTrue($this->checker->publicIsNoFollow('nofollow'));
        $this->assertTrue($this->checker->publicIsNoFollow('external nofollow'));
        $this->assertFalse($this->checker->publicIsNoFollow('external'));
        $this->assertFalse($this->checker->publicIsNoFollow(''));
    }

    public function testScanLinksWithNoFollow(): void
    {
        $html = '<a href="https://example.com" rel="nofollow" target="_blank">Test</a>';
        $dom = HtmlDomParser::str_get_html($html);
        $pattern = '/example\.com/';

        $backlinks = $this->checker->publicScanLinks($dom, $pattern);

        $this->assertCount(1, $backlinks);
        $this->assertTrue($backlinks[0]->isNoFollow());
        $this->assertEquals('_blank', $backlinks[0]->getTarget());
    }

    /**
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     */
    public function testScanLinksWithInvalidPatterns(): void
    {
        // Test with an invalid pattern
        $invalidPattern = [
            // Empty pattern
            "",
            // Missing closing bracket
            "/[a-z",
            // Unescaped special character
            "/hello(world/",
            // Unbalanced parentheses
            "/(abc/",
            // Invalid range in character class
        ];

        foreach ($invalidPattern as $pattern) {
            $result = @$this->checker->publicScanLinks(
                HtmlDomParser::str_get_html("<a href='https://example.com'>Example</a>"),
                $pattern
            );
            $this->assertEmpty($result, "Failed to match invalid pattern: $pattern");
        }
    }

    public function testScanImages(): void
    {
        $html = '<img src="https://example.com/img.jpg" alt="Test Image">';
        $dom = HtmlDomParser::str_get_html($html);
        $pattern = '/example\.com/';

        $backlinks = $this->checker->publicScanImages($dom, $pattern);

        $this->assertCount(1, $backlinks);
        $this->assertFalse($backlinks[0]->isNoFollow());
        $this->assertEquals('', $backlinks[0]->getTarget());
        $this->assertEquals('Test Image', $backlinks[0]->getAnchor());
    }

    /**
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     */
    public function testScanImagesWithInvalidPatterns(): void
    {
        // Test with an invalid pattern
        $invalidPattern = [
            // Empty pattern
            "",
            // Missing closing bracket
            "/[a-z",
            // Unescaped special character
            "/hello(world/",
            // Unbalanced parentheses
            "/(abc/",
            // Invalid range in character class
        ];

        foreach ($invalidPattern as $pattern) {
            $result = @$this->checker->publicScanImages(
                HtmlDomParser::str_get_html("<img src='https://example.com/image.jpg' alt='Example Image'>"),
                $pattern
            );
            $this->assertEmpty($result, "Failed to match invalid pattern: $pattern");
        }
    }

    public function testGetBacklinksSuccess(): void
    {
        $response = new HttpResponse('https://test.com', 200, [], '<a href="https://example.com">Test</a>', true, '');

        $this->checker->method('browsePage')
            ->willReturn($response);

        $result = $this->checker->getBacklinks('https://test.com', '/example\.com/', true, false, false);

        $this->assertInstanceOf(BacklinkData::class, $result);
        $this->assertSame($response, $result->getResponse());
        $this->assertCount(1, $result->getBacklinks());
    }

    public function testGetBacklinksFailure(): void
    {
        $response = new HttpResponse('https://example.com/', 404, [], 'Not Found', false, '');

        $this->checker->method('browsePage')
            ->willReturn($response);

        $result = $this->checker->getBacklinks('https://example.com/', '/test\.com/', true, false, false);

        $this->assertInstanceOf(BacklinkData::class, $result);
        $this->assertSame($response, $result->getResponse());
        $this->assertEmpty($result->getBacklinks());
    }
}
