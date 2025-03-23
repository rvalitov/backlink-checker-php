<?php

//phpcs:ignore
declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker\Backlink;
use Valitov\BacklinkChecker\BacklinkData;
use Valitov\BacklinkChecker\HttpResponse;

#[Group('offline')]
#[CoversClass(BacklinkData::class)]
#[UsesClass(Backlink::class)]
#[UsesClass(HttpResponse::class)]
class BacklinkDataTest extends TestCase //phpcs:ignore
{
    private function createTestHttpResponse(): HttpResponse
    {
        return new HttpResponse(
            'https://example.com',
            200,
            ['Content-Type' => ['text/html']],
            '<html><body>Test</body></html>',
            true,
            ''
        );
    }

    private function createTestBacklink(): Backlink
    {
        return new Backlink(
            'https://example.com/page',
            'Click here',
            false,
            '_blank',
            'a'
        );
    }

    public function testConstructorAndGetters(): void
    {
        $response = $this->createTestHttpResponse();
        $backlinks = [
            $this->createTestBacklink(),
            new Backlink('https://example.com/other', 'More', true, '', 'a')
        ];

        $backlinkData = new BacklinkData($response, $backlinks);

        $this->assertSame($response, $backlinkData->getResponse());
        $this->assertSame($backlinks, $backlinkData->getBacklinks());
        $this->assertCount(2, $backlinkData->getBacklinks());
    }

    public function testWithEmptyBacklinks(): void
    {
        $response = $this->createTestHttpResponse();
        $backlinks = [];

        $backlinkData = new BacklinkData($response, $backlinks);

        $this->assertSame($response, $backlinkData->getResponse());
        $this->assertEmpty($backlinkData->getBacklinks());
        $this->assertIsArray($backlinkData->getBacklinks());
    }

    public function testJsonSerialize(): void
    {
        $response = $this->createTestHttpResponse();
        $backlinks = [$this->createTestBacklink()];

        $backlinkData = new BacklinkData($response, $backlinks);

        $expected = [
            'response' => $response,
            'backlinks' => $backlinks
        ];

        $this->assertEquals($expected, $backlinkData->jsonSerialize());
    }

    public function testWithMultipleBacklinks(): void
    {
        $response = $this->createTestHttpResponse();
        $backlinks = [
            $this->createTestBacklink(),
            new Backlink('https://example.com/page2', 'Link 2', true, '_self', 'a'),
            new Backlink('https://example.com/image', 'Image', false, '', 'img')
        ];

        $backlinkData = new BacklinkData($response, $backlinks);

        $this->assertSame($response, $backlinkData->getResponse());
        $this->assertCount(3, $backlinkData->getBacklinks());
        $this->assertSame($backlinks[0], $backlinkData->getBacklinks()[0]);
        $this->assertSame($backlinks[1], $backlinkData->getBacklinks()[1]);
        $this->assertSame($backlinks[2], $backlinkData->getBacklinks()[2]);
    }

    public function testWithFailedResponse(): void
    {
        $response = new HttpResponse(
            'https://example.com',
            404,
            ['Content-Type' => ['text/html']],
            'Not Found',
            false,
            ''
        );
        $backlinks = [$this->createTestBacklink()];

        $backlinkData = new BacklinkData($response, $backlinks);

        $this->assertSame($response, $backlinkData->getResponse());
        $this->assertEquals(404, $backlinkData->getResponse()->getStatusCode());
        $this->assertFalse($backlinkData->getResponse()->isSuccess());
        $this->assertCount(1, $backlinkData->getBacklinks());
    }
}
