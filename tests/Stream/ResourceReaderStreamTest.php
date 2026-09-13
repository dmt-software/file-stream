<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Stream\ResourceReaderStream;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceReaderStream::class)]
final class ResourceReaderStreamTest extends TestCase
{
    public function testExposeResource(): void
    {
        $resource = $this->createStream('abc');

        $stream = new ResourceReaderStream($resource);

        $this->assertSame($resource, $stream->getStream());
    }

    public function testReadableAfterConstruction(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $this->assertTrue($stream->isReadable());
    }

    public function testNextReadsSingleCharacter(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $this->assertTrue($stream->next());
        $this->assertSame('a', $stream->current());

        $this->assertTrue($stream->next());
        $this->assertSame('b', $stream->current());

        $this->assertTrue($stream->next());
        $this->assertSame('c', $stream->current());

        $this->assertFalse($stream->next());
    }

    public function testCurrentAdvancesToFirstCharacterWhenNotStarted(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $this->assertSame('a', $stream->current());
    }

    public function testEmptyStreamCannotReturnCurrentValue(): void
    {
        $this->expectException(ReaderException::class);

        $stream = new ResourceReaderStream(
            $this->createStream('')
        );

        $stream->current();
    }

    public function testSeekableStreamIsRewindable(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $this->assertTrue($stream->isRewindable());
    }

    public function testRewindBeforeReadingDoesNothing(): void
    {
        [$resource, $write] = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP,
        );

        fwrite($write, 'abc');
        fclose($write);

        $stream = new ResourceReaderStream($resource);
        $stream->rewind();

        $this->assertSame('a', $stream->current());
    }

    public function testRewindRestartsReading(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $this->assertSame('a', $stream->current());

        $stream->next();

        $this->assertSame('b', $stream->current());

        $stream->rewind();

        $this->assertSame('a', $stream->current());
    }

    public function testNonSeekableStreamCannotRewindAfterReading(): void
    {
        $this->expectException(ReaderException::class);

        [$resource, $write] = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP,
        );

        fwrite($write, 'abc');
        fclose($write);

        $stream = new ResourceReaderStream($resource);

        $this->assertSame('a', $stream->current());

        $stream->rewind();
    }

    public function testEofAfterReadingCompleteStream(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('a')
        );

        $this->assertFalse($stream->eof());

        $this->assertTrue($stream->next());
        $this->assertFalse($stream->eof());

        $this->assertFalse($stream->next());
        $this->assertTrue($stream->eof());
    }

    public function testCloseMakesStreamUnreadable(): void
    {
        $resource = $this->createStream('abc');

        $stream = new ResourceReaderStream($resource);
        $stream->close();

        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
        $this->assertFalse(is_resource($resource));
    }

    public function testCloseIsIdempotent(): void
    {
        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $stream->close();
        $stream->close();

        $this->assertTrue($stream->eof());
    }

    public function testNextOnClosedStreamFails(): void
    {
        $this->expectException(ReaderException::class);

        $stream = new ResourceReaderStream(
            $this->createStream('abc')
        );

        $stream->close();
        $stream->next();
    }

    public function testExternallyClosedResourceIsNotReadable(): void
    {
        $resource = $this->createStream('abc');

        $stream = new ResourceReaderStream($resource);

        fclose($resource);

        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
        $this->assertFalse($stream->isRewindable());
    }

    public function testRejectUnreadableResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen(__DIR__ . '/../fixtures/stream.txt', 'a');

        try {
            new ResourceReaderStream($resource);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * @return resource
     */
    private function createStream(string $data): mixed
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, $data);
        rewind($resource);

        return $resource;
    }
}
