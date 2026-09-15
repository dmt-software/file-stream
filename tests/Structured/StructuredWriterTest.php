<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured;

use DMT\FileStream\Stream\JsonWriterStream;
use DMT\FileStream\Structured\StructuredWriter;
use DMT\FileStream\Structured\Template\JsonArrayContainerHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructuredWriter::class)]
final class StructuredWriterTest extends TestCase
{
    private const string OUTPUT_FILE = __DIR__ . '/../fixtures/output.txt';

    protected function tearDown(): void
    {
        $handle = fopen(self::OUTPUT_FILE, 'w');

        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    public function testWriteStructuredValues(): void
    {
        $writer = new StructuredWriter(
            new JsonWriterStream(fopen(self::OUTPUT_FILE, 'w')),
            new JsonArrayContainerHandler(),
        );

        $writer->write([
            '{"name":"first"}',
            '{"name":"second"}',
            '{"name":"third"}',
        ]);

        $this->assertSame(
            '[{"name":"first"},{"name":"second"},{"name":"third"}]',
            file_get_contents(self::OUTPUT_FILE)
        );
    }

    public function testWriteEmptyIterable(): void
    {
        $writer = new StructuredWriter(
            new JsonWriterStream(fopen(self::OUTPUT_FILE, 'w')),
            new JsonArrayContainerHandler(),
        );

        $writer->write([]);

        $this->assertSame('[]', file_get_contents(self::OUTPUT_FILE));
    }

    public function testWriteGenerator(): void
    {
        $writer = new StructuredWriter(
            new JsonWriterStream(
                fopen(self::OUTPUT_FILE, 'w')
            ),
            new JsonArrayContainerHandler(),
        );

        $writer->write(
            (static function (): iterable {
                yield '{"name":"first"}';
                yield '{"name":"second"}';
            })()
        );

        $this->assertSame(
            '[{"name":"first"},{"name":"second"}]',
            file_get_contents(self::OUTPUT_FILE)
        );
    }
}
