<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Csv;

use DMT\FileStream\Csv\CsvControl;
use DMT\FileStream\Reader\Csv\CsvLineParser;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvLineParser::class)]
final class CsvLineParserTest extends TestCase
{
    public function testParsesSingleLine(): void
    {
        $parser = $this->parser("name,age\nJane,42\n");

        $this->assertSame('name,age', $parser->parse());
        $this->assertSame('Jane,42', $parser->parse());
        $this->assertNull($parser->parse());
    }

    public function testParsesQuotedDelimiter(): void
    {
        $parser = $this->parser("\"Doe, Jane\",42\n");

        $this->assertSame('"Doe, Jane",42', $parser->parse());
    }

    public function testParsesMultilineRecord(): void
    {
        $parser = new CsvLineParser(
            stream: fopen(dirname(__DIR__, 2) . '/fixtures/csv/multiline.csv', 'r'),
            control: new CsvControl()
        );

        $this->assertSame('name,description', $parser->parse());
        $this->assertSame("Jane,\"first line\nsecond line\"", $parser->parse());
        $this->assertSame('John,"single line"', $parser->parse());
    }

    public function testAllowsQuoteInsideQuotedField(): void
    {
        $parser = $this->parser("\"this is 12\" wide\",123\n");

        $this->assertSame('"this is 12" wide",123', $parser->parse());
    }

    public function testAllowsEscapedEnclosure(): void
    {
        $parser = $this->parser(
            "\"12\\\"\",123\n",
            new CsvControl(escape: '\\')
        );

        $this->assertSame('"12\"",123', $parser->parse());
    }

    public function testReturnsEmptyRow(): void
    {
        $stream = fopen(dirname(__DIR__, 2) . '/fixtures/csv/empty-row.csv', 'r');

        $this->assertIsResource($stream);

        $parser = new CsvLineParser(
            stream: fopen(dirname(__DIR__, 2) . '/fixtures/csv/empty-row.csv', 'r'),
            control: new CsvControl()
        );

        $this->assertSame('name,age', $parser->parse());
        $this->assertSame('', $parser->parse());
        $this->assertSame('Jane,42', $parser->parse());
    }

    public function testParsesCustomDelimiterAndEnclosure(): void
    {
        $parser = $this->parser(
            "'Doe; Jane';42\r\n",
            new CsvControl(
                delimiter: ';',
                enclosure: "'",
                lineEnding: "\r\n"
            )
        );

        $this->assertSame("'Doe; Jane';42", $parser->parse());
    }

    public function testReturnsLastLineWithoutLineEnding(): void
    {
        $parser = $this->parser('Jane,42');

        $this->assertSame('Jane,42', $parser->parse());
        $this->assertNull($parser->parse());
    }

    public function testRejectsInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Stream must be a resource');

        new CsvLineParser(
            stream: 'not-a-resource',
            control: new CsvControl()
        );
    }

    private function parser(string $data, ?CsvControl $control = null): CsvLineParser
    {
        $stream = fopen('php://temp', 'r+');

        $this->assertIsResource($stream);

        fwrite($stream, $data);
        rewind($stream);

        return new CsvLineParser(
            stream: $stream,
            control: $control ?? new CsvControl()
        );
    }
}
