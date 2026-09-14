<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record\Boundary;

use DMT\FileStream\Config\CsvReaderConfig;
use DMT\FileStream\Record\Boundary\CsvRecordBoundary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvRecordBoundary::class)]
final class CsvRecordBoundaryTest extends TestCase
{
    #[DataProvider('provideCompleteRecords')]
    public function testDetectCompleteRecord(string $data): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig());

        $this->assertTrue($boundary->isBoundary($data));
    }

    #[DataProvider('provideIncompleteRecords')]
    public function testDetectIncompleteRecord(string $data): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig());

        $this->assertFalse($boundary->isBoundary($data));
    }

    public function testDetectMultilineEnclosedRecord(): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig());

        $this->assertFalse($boundary->isBoundary("\"John\n"));
        $this->assertTrue($boundary->isBoundary("\"John\nDoe\",42\n"));
    }

    public function testEscapedEnclosureDoesNotCloseRecord(): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig());

        $this->assertTrue($boundary->isBoundary("\"John \"\"Johnny\"\" Doe\",42\n"));
    }

    public function testUseConfiguredDelimiter(): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig(delimiter: ';'));

        $this->assertTrue($boundary->isBoundary("\"John;Doe\";42\n"));
        $this->assertFalse($boundary->isBoundary("\"John;Doe\",\n"));
    }

    public function testUseConfiguredEnclosure(): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig(enclosure: "'"));

        $this->assertTrue($boundary->isBoundary("'John,Doe',42\n"));
        $this->assertFalse($boundary->isBoundary("'John,Doe\n"));
    }

    public function testUseConfiguredEscapeCharacter(): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig(escape: '\\'));

        $this->assertTrue($boundary->isBoundary("\"John \\\"Johnny\\\" Doe\",42\n"));
        $this->assertFalse($boundary->isBoundary("\"John \\\"Johnny\\\" Doe\",42\\n"));
    }

    public function testUseConfiguredLineEnding(): void
    {
        $boundary = new CsvRecordBoundary(new CsvReaderConfig(lineEnding: "\r\n"));

        $this->assertFalse($boundary->isBoundary("John,Doe\n"));
        $this->assertTrue($boundary->isBoundary("John,Doe\r\n"));
    }

    public static function provideCompleteRecords(): iterable
    {
        return [
            'plain record' => ["John,Doe,42\n"],
            'empty record' => ["\n"],
            'enclosed column' => ["\"John,Doe\",42\n"],
            'multiple enclosed columns' => ["\"John\",\"Doe\",42\n"],
            'empty enclosed column' => ["\"\",42\n"],
            'escaped enclosure' => ["\"John \"\"Johnny\"\" Doe\",42\n"],
        ];
    }

    public static function provideIncompleteRecords(): iterable
    {
        return [
            'without line ending' => ['John,Doe,42'],
            'open enclosure' => ["\"John,Doe\n"],
            'partial enclosed record without line ending' => ['"John,Doe'],
            'empty string' => [''],
        ];
    }
}
