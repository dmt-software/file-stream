<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record;

use DMT\FileStream\Record\RecordWriter;
use DMT\FileStream\Stream\ResourceWriterStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordWriter::class)]
final class RecordWriterTest extends TestCase
{
    private const string OUTPUT_FILE = __DIR__ . '/../fixtures/output.txt';

    protected function tearDown(): void
    {
        $handle = fopen(self::OUTPUT_FILE, 'w');

        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    public function testWriteRecordsSequentially(): void
    {
        $writer = new RecordWriter(
            new ResourceWriterStream(
                fopen(self::OUTPUT_FILE, 'w')
            )
        );

        $writer->write(["first\n", "second\n", "third\n"]);

        $this->assertSame(
            "first\nsecond\nthird\n",
            file_get_contents(self::OUTPUT_FILE)
        );
    }

    public function testWriteEmptyIterable(): void
    {
        $writer = new RecordWriter(
            new ResourceWriterStream(
                fopen(self::OUTPUT_FILE, 'w')
            )
        );

        $writer->write([]);

        $this->assertSame('', file_get_contents(self::OUTPUT_FILE));
    }

    public function testWriteGenerator(): void
    {
        $writer = new RecordWriter(
            new ResourceWriterStream(
                fopen(self::OUTPUT_FILE, 'w')
            )
        );

        $writer->write(
            (static function (): iterable {
                yield "first\n";
                yield "second\n";
            })()
        );

        $this->assertSame(
            "first\nsecond\n",
            file_get_contents(self::OUTPUT_FILE)
        );
    }
}
