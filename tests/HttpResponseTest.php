<?php

//phpcs:ignore
declare(strict_types=1);
require_once __DIR__ . '/../src/BacklinkChecker/HttpResponse.php';

use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker;

final class HttpResponseTest extends TestCase //phpcs:ignore
{
    public function testJSON()
    {
        $testImageFilename = __DIR__ . "/data/test.jpg";
        $base64Prefix = "data:image/jpeg;base64,";
        $base64PrefixJson = json_encode($base64Prefix);
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