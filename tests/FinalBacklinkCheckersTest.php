<?php

//phpcs:ignore
declare(strict_types=1);

require_once __DIR__ . '/Config.php';

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker\BacklinkChecker;
use Valitov\BacklinkChecker\SimpleBacklinkChecker;
use Valitov\BacklinkChecker\ChromeBacklinkChecker;
use Valitov\BacklinkChecker\Backlink;
use Valitov\BacklinkChecker\BacklinkData;
use Valitov\BacklinkChecker\HttpResponse;

#[Group('online')]
#[CoversClass(SimpleBacklinkChecker::class)]
#[CoversClass(ChromeBacklinkChecker::class)]
#[UsesClass(Backlink::class)]
#[UsesClass(BacklinkData::class)]
#[UsesClass(HttpResponse::class)]
final class FinalBacklinkCheckersTest extends TestCase //phpcs:ignore
{
    /**
     * @var BacklinkChecker[]
     */
    private array $checkers;

    protected function setUp(): void
    {
        $this->checkers = [
            "simple" => new SimpleBacklinkChecker(),
            "chrome" => new ChromeBacklinkChecker(),
        ];
    }

    /**
     * This method tests that the Chrome mode detects more backlinks than the Simple mode.
     * This happens because the Chrome mode detects dynamic javascript-generated content.
     * @return void
     */
    public function testDynamicallyGeneratedLinks(): void
    {
        $url = Config::TEST_HOST . "js.html";
        $pattern = "@.*@";

        // Chrome mode should detect two backlinks
        $result = $this->checkers["chrome"]->getBacklinks($url, $pattern, true, false, false);
        $this->checkChromeResponse($result, $url, 2);

        // Simple mode should detect only one backlink
        $result = $this->checkers["simple"]->getBacklinks($url, $pattern, true, false, false);
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
        $result = $this->checkers["chrome"]->getBacklinks($url, $pattern, true, false, false);
        $this->checkChromeResponse($result, $url, 1);

        $result = $this->checkers["simple"]->getBacklinks($url, $pattern, true, false, false);
        $this->checkSimpleResponse($result, $url, 1);
    }

    /**
     * Checks the response of the Chrome mode
     * @param BacklinkData $data The response data
     * @param string $url The expected URL
     * @param int $expectedCount The expected number of backlinks
     * @return void
     */
    private function checkChromeResponse(BacklinkData $data, string $url, int $expectedCount): void
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
     * @param BacklinkData $data The response data
     * @param string $url The expected URL
     * @param int $expectedCount The expected number of backlinks
     * @return void
     */
    private function checkSimpleResponse(BacklinkData $data, string $url, int $expectedCount): void
    {
        $backlinks = $data->getBacklinks();
        $this->assertCount(
            $expectedCount,
            $backlinks,
            "Expected $expectedCount backlinks for $url",
        );
    }

    public function testLinkAttributes(): void
    {
        $checkList = [
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test1$@",
                "target" => "",
                "noFollow" => true,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test2$@",
                "target" => "_blank",
                "noFollow" => false,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test3$@",
                "target" => "_blank",
                "noFollow" => false,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test4$@",
                "target" => "",
                "noFollow" => false,
            ],
            [
                "url" => Config::TEST_HOST . "follow.html",
                "pattern" => "@^http(s)?://(www\.)?walitoff\.com/test5$@",
                "target" => "",
                "noFollow" => false,
            ],
        ];

        foreach ($this->checkers as $type => $checker) {
            foreach ($checkList as $id => $check) {
                $this->assertArrayHasKey("url", $check);
                $this->assertArrayHasKey("pattern", $check);
                $url = $check["url"];
                $pattern = $check["pattern"];
                // @phpstan-ignore method.alreadyNarrowedType
                $this->assertNotEmpty($url);
                // @phpstan-ignore method.alreadyNarrowedType
                $this->assertNotEmpty($pattern);
                $result = $checker->getBacklinks($url, $pattern);
                $response = $result->getResponse();
                $this->assertTrue($response->isSuccess(), "Failed to read webpage $url with $type checker");
                $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url with $type checker");
                $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url with $type checker");

                $backlinks = $result->getBacklinks();
                $this->assertCount(1, $backlinks, "Expected 1 backlinks for $url but got " . count($backlinks) . " with $type checker");

                foreach ($backlinks as $backlink) {
                    $this->assertNotEmpty($backlink->getBacklink(), "Failed to get backlink #$id for $url with $type checker");
                    $this->assertEquals(
                        $check["noFollow"],
                        $backlink->isNoFollow(),
                        "Failed to match 'noFollow' #$id for $url with $type checker",
                    );
                    $this->assertEquals(
                        $check["target"],
                        $backlink->getTarget(),
                        "Failed to match 'target' #$id for $url with $type checker",
                    );
                }
            }
        }
    }

    public function testInvalidPattern(): void
    {
        foreach ($this->checkers as $type => $checker) {
            $exceptionFired = false;
            try {
                $checker->getBacklinks(Config::TEST_HOST . "simple.html", "abc");
            } catch (\InvalidArgumentException) {
                $exceptionFired = true;
            }
            $this->assertTrue($exceptionFired, "InvalidArgumentException was not thrown for invalid pattern in $type checker");
        }
    }

    public function testEmptyHtml(): void
    {
        foreach ($this->checkers as $type => $checker) {
            $result = $checker->getBacklinks(Config::TEST_HOST . "empty.html", "@abc@");
            $response = $result->getResponse();
            $this->assertTrue($response->isSuccess(), "Response should be successful for $type checker");
            $this->assertEquals(200, $response->getStatusCode(), "Status code should be 200 for $type checker");
            $this->assertEmpty($result->getBacklinks(), "Backlinks should be empty for $type checker");
        }
    }

    public function testNotFoundUrl(): void
    {
        foreach ($this->checkers as $type => $checker) {
            $result = $checker->getBacklinks(Config::TEST_HOST . "missing", "@abc@");
            $response = $result->getResponse();
            $this->assertFalse($response->isSuccess(), "Response should not be successful for $type checker");
            $this->assertEquals(404, $response->getStatusCode(), "Status code should be 404 for $type checker");
            $this->assertEmpty($result->getBacklinks(), "Backlinks should be empty for $type checker");
        }
    }

    public function testInvalidProtocol(): void
    {
        foreach ($this->checkers as $type => $checker) {
            $exceptionFired = false;
            try {
                $checker->getBacklinks("ppp://localhost:8080/missing", "@abc@");
            } catch (RuntimeException) {
                $exceptionFired = true;
            }
            $this->assertTrue($exceptionFired, "RuntimeException was not thrown for invalid protocol in $type checker");
        }
    }

    public function testBasicLinksTest(): void
    {
        $testData = [
        [
            "url" => Config::TEST_HOST . "noLinks.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 0,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "simple.html",
            "pattern" => "@^https://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "simple.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 2,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "single.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        [
            "url" => Config::TEST_HOST . "emptyAnchor.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "images.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => true,
            "scanImages" => true,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "images.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 0,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "images.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 1,
            "scanLinks" => false,
            "scanImages" => true,
            "emptyAnchor" => true,
        ],
        [
            "url" => Config::TEST_HOST . "noLinks.html",
            "pattern" => "@^http(s)?://(www\.)?walitoff\.com.*@",
            "backlinks" => 0,
            "scanLinks" => true,
            "scanImages" => false,
            "emptyAnchor" => false,
        ],
        ];

        $properties = [
            "backlink",
            "anchor",
            "noFollow",
            "target",
            "tag",
        ];

        foreach ($testData as $testItem) {
            $this->assertArrayHasKey("url", $testItem);
            $url = $testItem["url"];
            $this->assertArrayHasKey("pattern", $testItem);
            $pattern = $testItem["pattern"];
            $this->assertArrayHasKey("backlinks", $testItem);
            $backlinksCount = $testItem["backlinks"];
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertNotEmpty($url);
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertIsString($pattern);
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertIsInt($backlinksCount);
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertIsString($url);
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertNotEmpty($pattern);
            $this->assertGreaterThanOrEqual(0, $backlinksCount);

            foreach ($this->checkers as $type => $checker) {
                $result = $checker->getBacklinks($url, $pattern, $testItem["scanLinks"], $testItem["scanImages"]);
                $response = $result->getResponse();
                $this->assertTrue($response->isSuccess(), "Failed to read webpage $url with $type checker");
                $this->assertNotEmpty($response->getResponse(), "Failed to get response from $url with $type checker");
                $this->assertNotEmpty($response->getHeaders(), "Failed to get headers from $url with $type checker");
                $this->assertEquals(200, $response->getStatusCode(), "Status code should be 200 for $url with $type checker");
                $this->assertEquals($url, $response->getUrl(), "URL should match for $url with $type checker");

                $json = $result->jsonSerialize();
                $this->assertNotEmpty($json, "Failed to get jsonSerialize for $url with $type checker");
                foreach (
                    [
                        "backlinks",
                        "response",
                    ] as $property
                ) {
                    $this->assertArrayHasKey($property, $json, "Serialize for $url must contain '$property' property with $type checker");
                }
                $backlinks = $result->getBacklinks();
                $this->assertCount(
                    $backlinksCount,
                    $backlinks,
                    "Expected $backlinksCount backlinks for $url but got " . count($backlinks) . " with $type checker",
                );
                if ($backlinksCount > 0) {
                    foreach ($backlinks as $id => $backlink) {
                        $this->assertNotEmpty($backlink->getBacklink(), "Failed to get backlink $id for $url with $type checker");
                        if (!$testItem["emptyAnchor"]) {
                            $this->assertNotEmpty($backlink->getAnchor(), "Failed to get anchor $id for $url with $type checker");
                        } else {
                            $this->assertEmpty($backlink->getAnchor(), "Failed to get empty anchor $id for $url with $type checker");
                        }

                        $this->assertNotEmpty($backlink->getTag(), "Failed to get tag $id for $url with $type checker");
                        $array = $backlink->jsonSerialize();
                        $this->assertNotEmpty($array, "Failed to get jsonSerialize for $url with $type checker");

                        foreach ($properties as $property) {
                            $this->assertArrayHasKey(
                                $property,
                                $array,
                                "Serialize for $url must contain '$property' property with $type checker",
                            );
                        }
                    }
                }
            }
        }
    }

    public function testEmptyUrl(): void
    {
        foreach ($this->checkers as $type => $checker) {
            $exceptionFired = false;
            try {
                $checker->getBacklinks("", "@abc@");
            } catch (\Exception) {
                $exceptionFired = true;
            }
            $this->assertTrue($exceptionFired, "Exception was not thrown for empty URL in $type checker");
        }
    }
}
