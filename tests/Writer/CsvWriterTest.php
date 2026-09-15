<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer;

use ArrayObject;
use DMT\FileStream\Config\CsvWriterConfig;
use DMT\FileStream\Record\Mapping\Csv\PredefinedNamedColumnMapper;
use DMT\FileStream\Stream\ResourceWriterStream;
use DMT\FileStream\Writer\CsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvWriter::class)]
#[Group('integration')]
final class CsvWriterTest extends TestCase
{
    private const string OUTPUT_FILE = __DIR__ . '/../fixtures/csv/output.csv';

    protected function tearDown(): void
    {
        $handle = fopen(self::OUTPUT_FILE, 'w');

        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    public function testWriteCsvRecords(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new CsvWriter(
            new ResourceWriterStream($resource),
            new CsvWriterConfig(
                columnMapper: new PredefinedNamedColumnMapper([
                    'firstName',
                    'lastName',
                    'age',
                ])
            )
        );

        $writer->write([
            new ArrayObject([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => 42,
            ]),
            new ArrayObject([
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'age' => 35,
            ]),
        ]);

        $this->assertSame(
            "John,Doe,42\nJane,Smith,35\n",
            file_get_contents(self::OUTPUT_FILE)
        );
    }
}
