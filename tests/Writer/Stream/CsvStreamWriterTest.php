<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Stream;

use DMT\FileStream\Csv\CsvControl;
use DMT\FileStream\Writer\Stream\CsvStreamWriter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvStreamWriter::class)]
final class CsvStreamWriterTest extends TestCase
{
    public function testWritesCsvRecordWithConfiguredLineEnding(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new CsvStreamWriter(
            stream: $stream,
            control: new CsvControl(
                lineEnding: "\r\n"
            )
        );

        $writer->write('PHP,1995');

        rewind($stream);

        $this->assertSame(
            "PHP,1995\r\n",
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testWritesMultipleRecords(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new CsvStreamWriter(
            stream: $stream,
            control: new CsvControl(
                lineEnding: "\n"
            )
        );

        $writer->write('PHP,1995');
        $writer->write('JavaScript,1995');

        rewind($stream);

        $this->assertSame(
            "PHP,1995\nJavaScript,1995\n",
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testWritesUtf8DataCompletely(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new CsvStreamWriter(
            stream: $stream,
            control: new CsvControl()
        );

        $writer->write('name,René');

        rewind($stream);

        $this->assertSame(
            "name,René\n",
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testThrowsWhenStreamCannotBeWritten(): void
    {
        $stream = fopen('php://memory', 'r');

        $writer = new CsvStreamWriter(
            stream: $stream,
            control: new CsvControl()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs('Unable to write CSV record');

        @$writer->write('PHP,1995');

        fclose($stream);
    }


    public function testRejectsInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Stream must be a resource');

        new CsvStreamWriter(
            stream: null,
            control: new CsvControl()
        );
    }
}
