<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use DMT\FileStream\Stream\XmlWriterStream;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use XMLWriter;

#[CoversClass(XmlWriterStream::class)]
final class XmlWriterStreamTest extends TestCase
{
    public function testExposeXmlWriter(): void
    {
        $stream = new XmlWriterStream(fopen('php://memory', 'w+'));

        $this->assertInstanceOf(XMLWriter::class, $stream->getStream());
    }

    public function testWritableAfterConstruction(): void
    {
        $stream = new XmlWriterStream(fopen('php://memory', 'w+'));

        $this->assertTrue($stream->isWritable());
    }

    public function testWriteBuffersXmlData(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource, bufferSize: 1024);
        $stream->write('<item>value</item>');

        rewind($resource);

        $this->assertSame('', stream_get_contents($resource));
    }

    public function testFlushWritesBufferedXmlData(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource, bufferSize: 1024);

        $stream->write('<item>value</item>');
        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '<item>value</item>',
            stream_get_contents($resource)
        );
    }

    public function testBufferFlushesWhenLimitIsReached(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource, bufferSize: 4);
        $stream->write('<item />');

        rewind($resource);

        $this->assertSame('<item />', stream_get_contents($resource));
    }

    public function testWriteThroughXmlWriter(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource);

        $writer = $stream->getStream();
        $writer->startElement('item');
        $writer->text('value');
        $writer->endElement();

        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '<item>value</item>',
            stream_get_contents($resource)
        );
    }

    public function testWriteDocumentThroughXmlWriter(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource);

        $writer = $stream->getStream();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('root');
        $writer->writeElement('item', 'value');
        $writer->endElement();
        $writer->endDocument();

        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<root><item>value</item></root>' . "\n",
            stream_get_contents($resource)
        );
    }

    public function testRepeatedFlushPreservesOutput(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource);
        $stream->write('<first />');
        $stream->flush();
        $stream->write('<second />');
        $stream->flush();

        rewind($resource);

        $this->assertSame(
            '<first /><second />',
            stream_get_contents($resource)
        );
    }

    public function testFlushWithoutDataDoesNothing(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource);
        $stream->flush();

        rewind($resource);

        $this->assertSame('', stream_get_contents($resource));
    }

    public function testCloseMakesStreamUnwritable(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource);
        $stream->close();

        $this->assertFalse($stream->isWritable());
        $this->assertFalse(is_resource($resource));
    }

    public function testCloseIsIdempotent(): void
    {
        $stream = new XmlWriterStream(fopen('php://memory', 'w+'));
        $stream->close();
        $stream->close();

        $this->assertFalse($stream->isWritable());
    }

    public function testWriteOnClosedStreamFails(): void
    {
        $this->expectException(WriterException::class);

        $stream = new XmlWriterStream(fopen('php://memory', 'w+'));
        $stream->close();
        $stream->write('<item />');
    }

    public function testFlushOnClosedStreamFails(): void
    {
        $this->expectException(WriterException::class);

        $stream = new XmlWriterStream(fopen('php://memory', 'w+'));
        $stream->close();
        $stream->flush();
    }

    public function testExternallyClosedResourceIsNotWritable(): void
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new XmlWriterStream($resource);

        fclose($resource);

        $this->assertFalse($stream->isWritable());
    }

    public function testRejectReadOnlyResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen(__DIR__ . '/../fixtures/xml/stream.xml', 'r',);

        try {
            new XmlWriterStream($resource);
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

        new XmlWriterStream(fopen('php://memory', 'w+'), bufferSize: 0);
    }
}
