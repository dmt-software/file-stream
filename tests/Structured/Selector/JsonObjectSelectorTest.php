<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured\Selector;

use DMT\FileStream\Stream\JsonReaderStream;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Structured\Path\DotSeparatedPath;
use DMT\FileStream\Structured\Selector\JsonObjectSelector;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonObjectSelector::class)]
final class JsonObjectSelectorTest extends TestCase
{
    public function testSelectRootObject(): void
    {
        $stream = $this->createStream('{"name":"John"}');

        $selector = new JsonObjectSelector(new DotSeparatedPath('.'));

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('{"name":"John"}', $stream->current());
    }

    public function testSelectNestedObject(): void
    {
        $stream = $this->createStream('{"response":{"data":{"name":"John"}}}');

        $selector = new JsonObjectSelector(new DotSeparatedPath('.response.data'));

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('{"name":"John"}', $stream->current());
    }

    public function testSelectObjectInsideNamedArray(): void
    {
        $stream = $this->createStream(
            '{"languages":[{"name":"PHP"},{"name":"JavaScript"}]}'
        );

        $selector = new JsonObjectSelector(new DotSeparatedPath('.languages'));

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('{"name":"PHP"}', $stream->current());
    }

    public function testSelectSubsequentObjectsAtSamePath(): void
    {
        $stream = $this->createStream(
            '{"languages":[{"name":"PHP"},{"name":"JavaScript"}]}'
        );

        $selector = new JsonObjectSelector(new DotSeparatedPath('.languages'));

        $this->assertTrue($selector->selectNext($stream));

        $this->assertSame('{"name":"PHP"}', $stream->current());
        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('{"name":"JavaScript"}', $stream->current());
    }

    public function testReturnFalseWhenNoMatchingObjectExists(): void
    {
        $stream = $this->createStream(
            '{"response":{"data":{"name":"John"}}}'
        );

        $selector = new JsonObjectSelector(new DotSeparatedPath('.missing'));

        $this->assertFalse($selector->selectNext($stream));
    }

    public function testReturnFalseAfterLastMatchingObject(): void
    {
        $stream = $this->createStream('{"languages":[{"name":"PHP"}]}');

        $selector = new JsonObjectSelector(new DotSeparatedPath('.languages'));

        $this->assertTrue($selector->selectNext($stream));
        $this->assertFalse($selector->selectNext($stream));
    }

    public function testSelectNestedObjectInsideArrayObject(): void
    {
        $stream = $this->createStream(
            '{"response":{"languages":[{"meta":{"name":"PHP"}}]}}'
        );

        $selector = new JsonObjectSelector(
            new DotSeparatedPath('.response.languages.meta')
        );

        $this->assertTrue($selector->selectNext($stream));
        $this->assertSame('{"name":"PHP"}', $stream->current());
    }

    public function testRejectIncompatibleStream(): void
    {

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            'JsonObjectSelector requires a JsonReaderStream'
        );

        $stream = $this->createMock(ReadableStreamInterface::class);
        $stream
            ->method('getStream')
            ->willReturn(fopen('php://memory', 'r'));

        $selector = new JsonObjectSelector(new DotSeparatedPath());
        $selector->selectNext($stream);
    }

    private function createStream(string $json): JsonReaderStream
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, $json);
        rewind($resource);

        return new JsonReaderStream($resource);
    }
}
