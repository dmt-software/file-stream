<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Stream;

use DMT\FileStream\Writer\Parser\TemplateParserInterface;
use DMT\FileStream\Writer\Stream\XmlStreamWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use XMLWriter;

#[CoversClass(XmlStreamWriter::class)]
final class XmlStreamWriterTest extends TestCase
{
    public function testWritesDefaultResultElement(): void
    {
        $xmlWriter = XMLWriter::toMemory();

        $writer = new XmlStreamWriter($xmlWriter);
        $writer->prepare();
        $writer->write('<item>one</item>');
        $writer->write('<item>two</item>');
        $writer->finalize();

        $this->assertStringContainsString(
            '<result><item>one</item><item>two</item></result>',
            $xmlWriter->outputMemory()
        );
    }

    public function testWritesEmptyResultElement(): void
    {
        $xmlWriter = XMLWriter::toMemory();

        $writer = new XmlStreamWriter($xmlWriter);
        $writer->prepare();
        $writer->finalize();

        $this->assertStringContainsString(
            '<result/>',
            $xmlWriter->outputMemory()
        );
    }

    public function testWritesRawXml(): void
    {
        $xmlWriter = XMLWriter::toMemory();

        $writer = new XmlStreamWriter($xmlWriter);
        $writer->prepare();
        $writer->write(
            '<item id="1"><name>John</name></item>'
        );
        $writer->finalize();

        $this->assertStringContainsString(
            '<result><item id="1"><name>John</name></item></result>',
            $xmlWriter->outputMemory()
        );
    }

    public function testUsesTemplateParserDuringPrepareAndFinalize(): void
    {
        $xmlWriter = XMLWriter::toMemory();

        $template = $this->createMock(
            TemplateParserInterface::class
        );

        $template
            ->expects($this->once())
            ->method('copyToPlaceholder');

        $template
            ->expects($this->once())
            ->method('copyRemainder');

        $writer = new XmlStreamWriter(
            writer: $xmlWriter,
            template: $template
        );

        $writer->prepare();
        $writer->write('<item>value</item>');
        $writer->finalize();

        $this->assertStringContainsString(
            '<item>value</item>',
            $xmlWriter->outputMemory()
        );
    }

    public function testWritesUtf8Data(): void
    {
        $xmlWriter = XMLWriter::toMemory();

        $writer = new XmlStreamWriter($xmlWriter);
        $writer->prepare();
        $writer->write('<name>René</name>');
        $writer->finalize();

        $this->assertStringContainsString(
            '<result><name>René</name></result>',
            $xmlWriter->outputMemory()
        );
    }

    public function testThrowsWhenRawXmlCannotBeWritten(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Unable to write complete XML data');

        $xmlWriter = $this->createMock(XMLWriter::class);
        $xmlWriter
            ->expects($this->once())
            ->method('writeRaw')
            ->with('<item/>')
            ->willReturn(false);

        $writer = new XmlStreamWriter($xmlWriter);
        $writer->write('<item/>');
    }
}
