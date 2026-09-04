<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Parser;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Writer\Parser\XmlTemplateParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use XMLReader;
use XMLWriter;

#[CoversClass(XmlTemplateParser::class)]
final class XmlTemplateParserTest extends TestCase
{
    public function testCopiesTemplateUpToPlaceholder(): void
    {
        $reader = XMLReader::XML(
            '<root><meta version="1">Example</meta><items>{{items}}</items></root>'
        );

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser($reader, $writer);
        $parser->copyToPlaceholder();

        $this->assertSame(
            '<root><meta version="1">Example</meta><items>',
            $writer->outputMemory()
        );
    }

    public function testCopiesRemainderAfterPlaceholder(): void
    {
        $reader = XMLReader::XML(
            '<root><items>{{items}}</items><meta><count>2</count><status>ok</status></meta></root>'
        );

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser($reader, $writer);
        $parser->copyToPlaceholder();

        $writer->outputMemory();

        $parser->copyRemainder();

        $this->assertSame(
            '</items><meta><count>2</count><status>ok</status></meta></root>',
            $writer->outputMemory()
        );
    }

    public function testUsesCustomPlaceholder(): void
    {
        $reader = XMLReader::XML(
            '<root><items>__DATA__</items></root>'
        );

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser(
            reader: $reader,
            writer: $writer,
            placeholder: '__DATA__'
        );
        $parser->copyToPlaceholder();
        $parser->copyRemainder();

        $this->assertSame(
            '<root><items></items></root>',
            $writer->outputMemory()
        );
    }

    public function testCopiesAttributes(): void
    {
        $reader = XMLReader::XML(
            '<root id="123" active="yes"><items>{{items}}</items></root>'
        );

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser($reader, $writer);
        $parser->copyToPlaceholder();

        $this->assertSame(
            '<root id="123" active="yes"><items>',
            $writer->outputMemory()
        );
    }

    public function testCopiesEmptyElements(): void
    {
        $reader = XMLReader::XML(
            '<root><meta/><items>{{items}}</items></root>'
        );

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser($reader, $writer);
        $parser->copyToPlaceholder();

        $this->assertSame(
            '<root><meta/><items>',
            $writer->outputMemory()
        );
    }

    public function testCopiesCdataAndComments(): void
    {
        $reader = XMLReader::XML(
            '<root><!--comment--><meta><![CDATA[a < b]]></meta><items>{{items}}</items></root>'
        );

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser($reader, $writer);
        $parser->copyToPlaceholder();

        $this->assertSame(
            '<root><!--comment--><meta><![CDATA[a < b]]></meta><items>',
            $writer->outputMemory()
        );
    }

    public function testThrowsWhenPlaceholderDoesNotExist(): void
    {
        $this->expectException(NotFoundException::class);

        $parser = new XmlTemplateParser(
            reader: XMLReader::XML('<root><items></items></root>'),
            writer: XMLWriter::toMemory()
        );
        $parser->copyToPlaceholder();
    }

    public function testWrapsReaderFailureInParserException(): void
    {
        $reader = XMLReader::XML(
            '<root><items>{{items}}</items></root>'
        );
        $reader->close();

        $writer = XMLWriter::toMemory();

        $parser = new XmlTemplateParser(
            reader: $reader,
            writer: $writer
        );

        $this->expectException(ParserException::class);

        @$parser->copyToPlaceholder();
    }
}
