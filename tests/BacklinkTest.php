<?php

//phpcs:ignore
declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Valitov\BacklinkChecker\Backlink;

#[Group('offline')]
#[CoversClass(Backlink::class)]
final class BacklinkTest extends TestCase //phpcs:ignore
{
    private string $testBacklink = 'https://example.com/page';
    private string $testAnchor = 'Click here';
    private bool $testNoFollow = true;
    private string $testTarget = '_blank';
    private string $testTag = 'a';

    public function testConstructorAndGetters(): void
    {
        $backlink = new Backlink(
            $this->testBacklink,
            $this->testAnchor,
            $this->testNoFollow,
            $this->testTarget,
            $this->testTag
        );

        $this->assertEquals($this->testBacklink, $backlink->getBacklink());
        $this->assertEquals($this->testAnchor, $backlink->getAnchor());
        $this->assertEquals($this->testNoFollow, $backlink->isNoFollow());
        $this->assertEquals($this->testTarget, $backlink->getTarget());
        $this->assertEquals($this->testTag, $backlink->getTag());
    }

    public function testJsonSerialize(): void
    {
        $backlink = new Backlink(
            $this->testBacklink,
            $this->testAnchor,
            $this->testNoFollow,
            $this->testTarget,
            $this->testTag
        );

        $expected = [
            'backlink' => $this->testBacklink,
            'anchor' => $this->testAnchor,
            'noFollow' => $this->testNoFollow,
            'target' => $this->testTarget,
            'tag' => $this->testTag
        ];

        $this->assertEquals($expected, $backlink->jsonSerialize());
    }

    public function testWithNoFollowFalse(): void
    {
        $backlink = new Backlink(
            $this->testBacklink,
            $this->testAnchor,
            false,
            $this->testTarget,
            $this->testTag
        );

        $this->assertEquals($this->testBacklink, $backlink->getBacklink());
        $this->assertEquals($this->testAnchor, $backlink->getAnchor());
        $this->assertFalse($backlink->isNoFollow());
        $this->assertEquals($this->testTarget, $backlink->getTarget());
        $this->assertEquals($this->testTag, $backlink->getTag());
    }

    public function testWithImgTag(): void
    {
        $backlink = new Backlink(
            $this->testBacklink,
            $this->testAnchor,
            $this->testNoFollow,
            $this->testTarget,
            'img'
        );

        $this->assertEquals($this->testBacklink, $backlink->getBacklink());
        $this->assertEquals($this->testAnchor, $backlink->getAnchor());
        $this->assertEquals($this->testNoFollow, $backlink->isNoFollow());
        $this->assertEquals($this->testTarget, $backlink->getTarget());
        $this->assertEquals('img', $backlink->getTag());
    }

    public function testWithEmptyTarget(): void
    {
        $backlink = new Backlink(
            $this->testBacklink,
            $this->testAnchor,
            $this->testNoFollow,
            '',
            $this->testTag
        );

        $this->assertEquals($this->testBacklink, $backlink->getBacklink());
        $this->assertEquals($this->testAnchor, $backlink->getAnchor());
        $this->assertEquals($this->testNoFollow, $backlink->isNoFollow());
        $this->assertEquals('', $backlink->getTarget());
        $this->assertEquals($this->testTag, $backlink->getTag());
    }
}
