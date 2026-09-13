<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Stream\XmlReaderStream;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use XMLReader;

#[CoversClass(XmlReaderStream::class)]
final class XmlReaderStreamTest extends TestCase
{
    public function testExposeXmlReader(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root><item>value</item></root>')
        );

        $this->assertInstanceOf(XMLReader::class, $stream->getStream());
    }

    public function testReadableAfterConstruction(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $this->assertTrue($stream->isReadable());
    }

    public function testNextMovesToFirstNode(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $this->assertTrue($stream->next());
        $this->assertSame('root', $stream->getStream()->localName);
    }

    public function testCurrentAdvancesToFirstNode(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root><item>value</item></root>')
        );

        $this->assertSame(
            '<root><item>value</item></root>',
            $stream->current()
        );
    }

    public function testCurrentReturnsCurrentElement(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root><item>value</item></root>')
        );

        while ($stream->next()) {
            if ($stream->getStream()->localName === 'item') {
                break;
            }
        }

        $this->assertSame('<item>value</item>', $stream->current());
    }

    public function testEmptyStreamCannotReturnCurrentValue(): void
    {
        $this->expectException(ReaderException::class);

        $stream = new XmlReaderStream(
            $this->createStream('')
        );

        $stream->current();
    }

    public function testPhpStreamIsNotRewindable(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $this->assertFalse($stream->isRewindable());
    }

    public function testFileStreamIsRewindable(): void
    {
        $resource = fopen(__DIR__ . '/../fixtures/xml/stream.xml', 'r');

        $stream = new XmlReaderStream($resource);

        $this->assertTrue($stream->isRewindable());
    }

    public function testRewindBeforeReadingDoesNothing(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $stream->rewind();

        $this->assertTrue($stream->next());
        $this->assertSame('root', $stream->getStream()->localName);
    }

    public function testRewindRestartsXmlReader(): void
    {
        $stream = new XmlReaderStream(
            fopen(__DIR__ . '/../fixtures/xml/stream.xml', 'r')
        );

        $this->assertTrue($stream->next());
        $this->assertSame('root', $stream->getStream()->localName);

        $stream->rewind();

        $this->assertTrue($stream->next());
        $this->assertSame('root', $stream->getStream()->localName);
    }

    public function testEofAfterCompleteRead(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $this->assertFalse($stream->eof());

        while ($stream->next()) {
        }

        $this->assertTrue($stream->eof());
    }

    public function testCloseMakesStreamUnreadable(): void
    {
        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $stream->close();

        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
    }

    public function testNextOnClosedStreamFails(): void
    {
        $this->expectException(ReaderException::class);

        $stream = new XmlReaderStream(
            $this->createStream('<root />')
        );

        $stream->close();
        $stream->next();
    }

    public function testRejectUnreadableResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen(__DIR__ . '/../fixtures/xml/stream.xml', 'a',);

        try {
            new XmlReaderStream($resource);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * @return resource
     */
    private function createStream(string $xml): mixed
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, $xml);
        rewind($resource);

        return $resource;
    }
}
