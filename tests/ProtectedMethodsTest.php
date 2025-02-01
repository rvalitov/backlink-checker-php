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
use voku\helper\HtmlDomParser;

final class ProtectedMethodsTest extends TestCase //phpcs:ignore
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

    public function testGetRawBacklinks(): void
    {
        // Test the protected method getRawBacklinks
        $reflection = new ReflectionClass($this->checker);
        $method = $reflection->getMethod('getRawBacklinks');
        $method->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $method->invokeArgs($this->checker, [
            "<a href='https://example.com'>Example</a>",
            "",
            true,
            false,
        ]);

        // Test with invalid regex
        $invalidRegex = [
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
            $result = @$method->invokeArgs($this->checker, [
                "<a href='https://example.com'>Example</a>",
                $regex,
                true,
                false,
            ]);
            $this->assertEmpty($result, "Failed to match invalid regex: $regex");
        }

        // Test with valid regex
        $validRegex = [
            [
                // Match any URL
                "/https?:\/\/[^\s]+/",
                "<a href='https://example.com'>Example</a>"
            ],
            [
                // Match specific domain
                "/https?:\/\/(www\.)?example\.com/",
                "<a href='https://example.com'>Example</a>"
            ],
            [
                // Match URLs with query parameters
                "/https?:\/\/[^\s]+\?[^\s]+/",
                "<a href='https://example.com/test?query'>Example</a>"
            ],
            [
                // Match URLs with fragments
                "/https?:\/\/[^\s]+#[^\s]+/",
                "<a href='https://example.com#help'>Example</a>"
            ],
            [
                // Match relative URLs
                "/^\/[^\s]+/",
                "<a href='/example'>Example</a>"
            ]
        ];

        foreach ($validRegex as $item) {
            $regex = $item[0];
            $html = $item[1];
            $result = $method->invokeArgs($this->checker, [
                $html,
                $regex,
                true,
                false,
            ]);
            $this->assertNotEmpty($result, "Failed to match valid regex: $regex");
        }

        // Test with valid regex
        $validRegex = [
            [
                // Match specific domain
                "/https?:\/\/(www\.)?example\.com/",
                "<a href='https://example2.com'>Example</a>"
            ],
            [
                // Match URLs with query parameters
                "/https?:\/\/[^\s]+\?[^\s]+/",
                "<a href='https://example.com'>Example</a>"
            ],
            [
                // Match URLs with fragments
                "/https?:\/\/[^\s]+#[^\s]+/",
                "<a href='https://example.com'>Example</a>"
            ],
            [
                // Match relative URLs
                "/^\/[^\s]+/",
                "<a href='https://example.com'>Example</a>"
            ]
        ];

        foreach ($validRegex as $item) {
            $regex = $item[0];
            $html = $item[1];
            $result = $method->invokeArgs($this->checker, [
                $html,
                $regex,
                true,
                false,
            ]);
            $this->assertEmpty($result, "Failed to match valid regex: $regex");
        }

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
            // Link with mailto scheme
            "<a href='mailto:example@example.com'>Email Link</a>",
            // Link with tel scheme
            "<a href='tel:+1234567890'>Telephone Link</a>",
            // Link with fragment identifier
            "<a href='#section1'>Fragment Identifier</a>",
            // Link with query parameters
            "<a href='https://example.com/?param1=value1&param2=value2'>Query Parameters</a>",
        ];

        $regex = "/.+/";
        foreach ($htmlContents as $html) {
            $result = $method->invokeArgs($this->checker, [
                $html,
                $regex,
                true,
                false,
            ]);
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

    public function testScanLinks(): void
    {
        // Test the protected method scanLinks
        $reflection = new ReflectionClass($this->checker);
        $method = $reflection->getMethod('scanLinks');
        $method->setAccessible(true);

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
            $result = @$method->invokeArgs($this->checker, [
                HtmlDomParser::str_get_html("<a href='https://example.com'>Example</a>"),
                $pattern,
            ]);
            $this->assertEmpty($result, "Failed to match invalid pattern: $pattern");
        }
    }

    public function testScanImages(): void
    {
        // Test the protected method scanImages
        $reflection = new ReflectionClass($this->checker);
        $method = $reflection->getMethod('scanImages');
        $method->setAccessible(true);

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
            $result = @$method->invokeArgs($this->checker, [
                HtmlDomParser::str_get_html("<img src='https://example.com/image.jpg' alt='Example Image'>"),
                $pattern,
            ]);
            $this->assertEmpty($result, "Failed to match invalid pattern: $pattern");
        }
    }
}
