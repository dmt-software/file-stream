<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Factory;

use DMT\FileStream\Factory\JsonParserFactory;
use InvalidArgumentException;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;

final class JsonParserFactoryTest extends TestCase
{
    public const string FILE = __DIR__ . '/../Fixtures/json/programming.json';

    public function testFromString(): void
    {
        $reader = new JsonParserFactory()
            ->fromString(file_get_contents(self::FILE));
        $reader->read();

        $this->assertSame(JsonReader::OBJECT, $reader->type());
    }

    public function testFromStream(): void
    {
        $reader = new JsonParserFactory()
            ->fromStream(fopen(self::FILE, 'r'));
        $reader->read();

        $this->assertSame(JsonReader::OBJECT, $reader->type());
    }

    public function testFromFile(): void
    {
        $reader = new JsonParserFactory()->fromFile(self::FILE);
        $reader->read();

        $this->assertSame(JsonReader::OBJECT, $reader->type());
    }

    public function testConfigurationIsPassedToParser(): void
    {
        $reader = new JsonParserFactory(['options' => JsonReader::FLOATS_AS_STRINGS])
            ->fromString('{"number":12302307009823388264988346}');
        $reader->read();

        $this->assertIsString($d = $reader->value()['number']);
    }

    public function testFromStreamRejectsNonResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new JsonParserFactory()->fromStream('not-a-resource');
    }

    public function testFromFileRejectsMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new JsonParserFactory()->fromFile(dirname(__DIR__) . '/../Fixtures/json/missing.json');
    }

    public function testFromStringRejectsInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new JsonParserFactory()->fromString('{invalid json]');
    }

    public function testFromUnreadableStream(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $file = tempnam(sys_get_temp_dir(), 'json-reader');

        new JsonParserFactory()->fromStream(fopen($file, 'w'));
    }
}
