<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Stream\JsonReaderStream;
use InvalidArgumentException;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonReaderStream::class)]
final class JsonReaderStreamTest extends TestCase
{
    public function testExposeJsonReader(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );

        $this->assertInstanceOf(
            JsonReader::class,
            $stream->getStream()
        );
    }

    public function testReadableAfterConstruction(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );

        $this->assertTrue($stream->isReadable());
    }

    public function testReadNextNode(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );

        $this->assertTrue($stream->next());
    }

    public function testCurrentReturnsCompleteValueBeforeReading(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );

        $this->assertSame('{"name":"value"}', $stream->current());
    }

    public function testCurrentReturnsCurrentNodeValue(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );

        while ($stream->next()) {
            if ($stream->getStream()->name() === 'name') {
                break;
            }
        }

        $this->assertSame('"value"', $stream->current());
    }

    public function testEmptyStreamCannotReturnCurrentValue(): void
    {
        $this->expectException(ReaderException::class);

        $stream = new JsonReaderStream($this->createStream(''));
        $stream->current();
    }

    public function testSeekableStreamIsRewindable(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
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

        fwrite($write, '{"name":"value"}');
        fclose($write);

        $stream = new JsonReaderStream($resource);
        $stream->rewind();

        $this->assertTrue($stream->next());
    }

    public function testRewindRestartsJsonReader(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );

        $this->assertTrue($stream->next());

        $firstType = $stream->getStream()->type();

        $stream->rewind();

        $this->assertTrue($stream->next());
        $this->assertSame($firstType, $stream->getStream()->type());
    }

    public function testCloseMakesStreamUnreadable(): void
    {
        $resource = $this->createStream('{"name":"value"}');

        $stream = new JsonReaderStream($resource);
        $stream->close();

        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
        $this->assertFalse(is_resource($resource));
    }

    public function testCloseIsIdempotent(): void
    {
        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );
        $stream->close();
        $stream->close();

        $this->assertTrue($stream->eof());
    }

    public function testNextOnClosedStreamFails(): void
    {
        $this->expectException(ReaderException::class);

        $stream = new JsonReaderStream(
            $this->createStream('{"name":"value"}')
        );
        $stream->close();
        $stream->next();
    }

    public function testExternallyClosedResourceIsNotReadable(): void
    {
        $resource = $this->createStream('{"name":"value"}');

        $stream = new JsonReaderStream($resource);

        fclose($resource);

        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
        $this->assertFalse($stream->isRewindable());
    }

    public function testRejectUnreadableResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen(__DIR__ . '/../fixtures/json/stream.json', 'a');

        try {
            new JsonReaderStream($resource);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * @return resource
     */
    private function createStream(string $json): mixed
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, $json);
        rewind($resource);

        return $resource;
    }
}
