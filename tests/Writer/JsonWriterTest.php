<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer;

use DMT\FileStream\Config\JsonWriterConfig;
use DMT\FileStream\Stream\JsonWriterStream;
use DMT\FileStream\Structured\Template\JsonArrayContainerHandler;
use DMT\FileStream\Writer\JsonWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonWriter::class)]
#[Group('integration')]
final class JsonWriterTest extends TestCase
{
    private const string OUTPUT_FILE = __DIR__ . '/../fixtures/json/output.json';

    protected function tearDown(): void
    {
        $handle = fopen(self::OUTPUT_FILE, 'w');

        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    public function testWriteJsonObjects(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new JsonWriter(
            new JsonWriterStream($resource),
            new JsonWriterConfig(
                template: new JsonArrayContainerHandler()
            )
        );

        $writer->write([
            (object)[
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => 42,
            ],
            (object)[
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'age' => 35,
            ],
        ]);

        $this->assertSame(
            '[{"firstName":"John","lastName":"Doe","age":42},'
            . '{"firstName":"Jane","lastName":"Smith","age":35}]',
            file_get_contents(self::OUTPUT_FILE)
        );
    }

    public function testWriteEmptyJsonArray(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new JsonWriter(
            new JsonWriterStream($resource),
            new JsonWriterConfig(
                template: new JsonArrayContainerHandler()
            )
        );

        $writer->write([]);

        $this->assertSame(
            '[]',
            file_get_contents(self::OUTPUT_FILE)
        );
    }

    public function testWriteNestedJsonObject(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new JsonWriter(
            new JsonWriterStream($resource),
            new JsonWriterConfig(
                template: new JsonArrayContainerHandler()
            )
        );

        $writer->write([
            (object)[
                'name' => 'John',
                'address' => (object)[
                    'city' => 'Amsterdam',
                ],
            ],
        ]);

        $this->assertSame(
            '[{"name":"John","address":{"city":"Amsterdam"}}]',
            file_get_contents(self::OUTPUT_FILE)
        );
    }
}
