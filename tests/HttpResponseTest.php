<?php

//phpcs:ignore
declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker;

#[Group('offline')]
#[CoversClass(BacklinkChecker\HttpResponse::class)]
final class HttpResponseTest extends TestCase //phpcs:ignore
{
    private string $testUrl = 'https://example.com';
    private int $testStatusCode = 200;
    private array $testHeaders = [
        'Content-Type' => ['application/json'],
        'Cache-Control' => ['no-cache']
    ];
    private string $testResponse = '{"status":"ok"}';
    private bool $testSuccess = true;
    private string $testScreenshot = 'binary_data';

    public function testConstructorAndGetters()
    {
        $response = new BacklinkChecker\HttpResponse(
            $this->testUrl,
            $this->testStatusCode,
            $this->testHeaders,
            $this->testResponse,
            $this->testSuccess,
            $this->testScreenshot
        );

        $this->assertEquals($this->testUrl, $response->getUrl());
        $this->assertEquals($this->testStatusCode, $response->getStatusCode());
        $this->assertEquals($this->testHeaders, $response->getHeaders());
        $this->assertEquals($this->testResponse, $response->getResponse());
        $this->assertEquals($this->testSuccess, $response->isSuccess());
        $this->assertEquals($this->testScreenshot, $response->getScreenshot());
    }

    public function testJsonSerializeWithScreenshot()
    {
        $response = new BacklinkChecker\HttpResponse(
            $this->testUrl,
            $this->testStatusCode,
            $this->testHeaders,
            $this->testResponse,
            $this->testSuccess,
            $this->testScreenshot
        );

        $expected = [
            'url' => $this->testUrl,
            'statusCode' => $this->testStatusCode,
            'headers' => $this->testHeaders,
            'response' => $this->testResponse,
            'success' => $this->testSuccess,
            'screenshot' => 'data:image/jpeg;base64,' . base64_encode($this->testScreenshot)
        ];

        $this->assertEquals($expected, $response->jsonSerialize());
    }

    public function testJsonSerializeWithoutScreenshot()
    {
        $response = new BacklinkChecker\HttpResponse(
            $this->testUrl,
            $this->testStatusCode,
            $this->testHeaders,
            $this->testResponse,
            $this->testSuccess,
            ''
        );

        $expected = [
            'url' => $this->testUrl,
            'statusCode' => $this->testStatusCode,
            'headers' => $this->testHeaders,
            'response' => $this->testResponse,
            'success' => $this->testSuccess,
            'screenshot' => ''
        ];

        $this->assertEquals($expected, $response->jsonSerialize());
    }

    public function testFailedRequest()
    {
        $response = new BacklinkChecker\HttpResponse(
            $this->testUrl,
            404,
            $this->testHeaders,
            'Not Found',
            false,
            ''
        );

        $this->assertEquals($this->testUrl, $response->getUrl());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals($this->testHeaders, $response->getHeaders());
        $this->assertEquals('Not Found', $response->getResponse());
        $this->assertFalse($response->isSuccess());
        $this->assertEquals('', $response->getScreenshot());
    }

    public function testJsonSerializeComprehensive(): void
    {
        $testImageFilename = __DIR__ . "/data/test.jpg";
        $base64Prefix = "data:image/jpeg;base64,";
        $base64PrefixJson = json_encode($base64Prefix);
        $this->assertNotFalse($base64PrefixJson);
        //Remove first and last quotes
        $base64PrefixJson = mb_substr($base64PrefixJson, 1, -1);

        $this->assertTrue(file_exists($testImageFilename));
        $this->assertTrue(is_readable($testImageFilename));
        $testImage = file_get_contents($testImageFilename);
        $this->assertNotFalse($testImage);

        $response = new BacklinkChecker\HttpResponse(
            "http://example.com",
            200,
            [["Content-Type" => "text/html"]],
            "Hello, world!",
            true,
            $testImage,
        );
        $json = json_encode($response);
        $this->assertNotFalse($json);
        $this->assertJson($json);

        //Test that the screenshot is included in the JSON in base64 format
        $encodedImageBase64 = base64_encode($testImage);
        $encodedImage = json_encode($encodedImageBase64);
        $this->assertNotFalse($encodedImage);
        //Remove first and last quotes
        $encodedImage = mb_substr($encodedImage, 1, -1);
        $this->assertStringContainsString($base64PrefixJson . $encodedImage, $json);

        //Reverse the process
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey("screenshot", $decoded);
        $this->assertIsString($decoded["screenshot"]);
        $this->assertStringStartsWith($base64Prefix, $decoded["screenshot"]);
        $pureImage = mb_substr($decoded["screenshot"], mb_strlen($base64Prefix));
        $this->assertEquals($encodedImageBase64, $pureImage);

        //Decode the image
        $decodedImage = base64_decode($pureImage);
        $this->assertNotFalse($decodedImage);
        $this->assertEquals($testImage, $decodedImage);
    }
}
