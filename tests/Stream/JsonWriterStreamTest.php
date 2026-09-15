<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use DMT\FileStream\Stream\JsonWriterStream;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonWriterStream::class)]
final class JsonWriterStreamTest extends TestCase
{
    public function testExposeInternalBufferStream(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource);

        $this->assertIsResource($stream->getStream());
        $this->assertNotSame($resource, $stream->getStream());
    }

    public function testWritableAfterConstruction(): void
    {
        $stream = new JsonWriterStream(fopen('php://memory', 'w+'));

        $this->assertTrue($stream->isWritable());
    }

    public function testWriteSingleValue(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource, bufferSize: 1024);
        $stream->write('{"id":1}');
        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '{"id":1}',
            stream_get_contents($resource)
        );
    }

    public function testSeparateValuesWithComma(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource, bufferSize: 1024);
        $stream->write('{"id":1}');
        $stream->write('{"id":2}');
        $stream->write('{"id":3}');
        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '{"id":1},{"id":2},{"id":3}',
            stream_get_contents($resource)
        );
    }

    public function testWriteBuffersData(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource, bufferSize: 1024);
        $stream->write('{"id":1}');

        rewind($resource);

        $this->assertSame('', stream_get_contents($resource));
    }

    public function testBufferFlushesWhenLimitIsReached(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource, bufferSize: 8);
        $stream->write('{"id":1}');

        rewind($resource);

        $this->assertSame(
            '{"id":1}',
            stream_get_contents($resource)
        );
    }

    public function testDirectBufferWritesBypassValueSeparation(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource, bufferSize: 1024);

        fwrite($stream->getStream(), '[');

        $stream->write('{"id":1}');
        $stream->write('{"id":2}');

        fwrite($stream->getStream(), ']');

        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '[{"id":1},{"id":2}]',
            stream_get_contents($resource)
        );
    }

    public function testCloseFlushesBufferedData(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource, bufferSize: 1024);
        $stream->write('{"id":1}');
        $stream->close();

        $this->assertFalse(is_resource($resource));
    }

    public function testCloseMakesStreamUnwritable(): void
    {
        $stream = new JsonWriterStream(fopen('php://memory', 'w+'));

        $stream->close();

        $this->assertFalse($stream->isWritable());
    }

    public function testCloseIsIdempotent(): void
    {
        $stream = new JsonWriterStream(fopen('php://memory', 'w+'));

        $stream->close();
        $stream->close();

        $this->assertFalse($stream->isWritable());
    }

    public function testWriteOnClosedStreamFails(): void
    {
        $this->expectException(WriterException::class);

        $stream = new JsonWriterStream(fopen('php://memory', 'w+'));

        $stream->close();
        $stream->write('{"id":1}');
    }

    public function testFlushOnClosedStreamFails(): void
    {
        $this->expectException(WriterException::class);

        $stream = new JsonWriterStream(fopen('php://memory', 'w+'));

        $stream->close();
        $stream->flush();
    }

    public function testExternallyClosedResourceIsNotWritable(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new JsonWriterStream($resource);

        fclose($resource);

        $this->assertFalse($stream->isWritable());
    }

    public function testRejectUnreadableOutputMode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen(__DIR__ . '/../fixtures/stream.txt', 'r');

        try {
            new JsonWriterStream($resource);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    public function testRejectInvalidBufferSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            'Buffer limit must be greater than zero'
        );

        new JsonWriterStream(fopen('php://memory', 'w+'), bufferSize: 0);
    }
}