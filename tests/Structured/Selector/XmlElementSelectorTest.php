<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured\Selector;

use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Stream\XmlReaderStream;
use DMT\FileStream\Structured\Path\SlashSeparatedPath;
use DMT\FileStream\Structured\Selector\XmlElementSelector;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlElementSelector::class)]
final class XmlElementSelectorTest extends TestCase
{
    public function testSelectRootElement(): void
    {
        $stream = $this->createStream('<root><item>value</item></root>');

        $selector = new XmlElementSelector(new SlashSeparatedPath('/root'));

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame(
            '<root><item>value</item></root>',
            $stream->current()
        );
    }

    public function testSelectNestedElement(): void
    {
        $stream = $this->createStream(
            '<root><items><item>value</item></items></root>'
        );

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/items/item')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('<item>value</item>', $stream->current());
    }

    public function testSelectElementUsingWildcard(): void
    {
        $stream = $this->createStream(
            '<root><items><item>value</item></items></root>'
        );

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/./item')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('<item>value</item>', $stream->current());
    }

    public function testSelectSubsequentElementsAtSamePath(): void
    {
        $stream = $this->createStream(
            '<root><items><item>one</item><item>two</item></items></root>'
        );

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/items/item')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('<item>one</item>', $stream->current());

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('<item>two</item>', $stream->current());
    }

    public function testReturnFalseWhenNoMatchingElementExists(): void
    {
        $stream = $this->createStream('<root><item>value</item></root>');

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/missing')
        );

        $this->assertFalse($selector->selectNext($stream));
    }

    public function testReturnFalseAfterLastMatchingElement(): void
    {
        $stream = $this->createStream('<root><item>value</item></root>');

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/item')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertFalse($selector->selectNext($stream));
    }

    public function testTrackPathAfterLeavingSiblingElement(): void
    {
        $stream = $this->createStream(
            '<root><first><item>one</item></first><second><item>two</item></second></root>'
        );

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/second/item')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('<item>two</item>', $stream->current());
    }

    public function testUseLocalNameForNamespacedElement(): void
    {
        $stream = $this->createStream(
            '<root xmlns:x="urn:test"><x:item>value</x:item></root>'
        );

        $selector = new XmlElementSelector(
            new SlashSeparatedPath('/root/item')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame(
            '<x:item xmlns:x="urn:test">value</x:item>',
            $stream->current()
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRejectIncompatibleStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            'XmlElementSelector requires a XMLReaderStream'
        );

        $stream = $this->createMock(ReadableStreamInterface::class);
        $stream
            ->method('getStream')
            ->willReturn(fopen('php://memory', 'r'));

        $selector = new XmlElementSelector(new SlashSeparatedPath());
        $selector->selectNext($stream);
    }

    private function createStream(string $xml): XmlReaderStream
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, $xml);
        rewind($resource);

        return new XmlReaderStream($resource);
    }
}
