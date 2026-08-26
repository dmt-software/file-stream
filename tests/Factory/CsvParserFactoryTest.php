<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Factory;

use DMT\FileStream\Factory\CsvParserFactory;
use DMT\FileStream\Reader\Csv\CsvParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CsvParserFactoryTest extends TestCase
{
    public const string FILE = __DIR__ . '/../Fixtures/csv/programming.csv';

    public function testFromString(): void
    {
        $parser = new CsvParserFactory()
            ->fromString(file_get_contents(self::FILE));

        $this->assertSame(['name', 'since', 'by'], $parser->current());
    }

    public function testFromStream(): void
    {
        $parser = new CsvParserFactory()
            ->fromStream(fopen(self::FILE, 'r'));

        $this->assertSame(['name', 'since', 'by'], $parser->current());
    }

    public function testFromFile(): void
    {
        $parser = new CsvParserFactory()
            ->fromFile(self::FILE);

        $this->assertSame(['name', 'since', 'by'], $parser->current());
    }

    public function testConfigurationIsPassedToParser(): void
    {
        $parser = new CsvParserFactory(['delimiter' => ';'])
            ->fromString("name;since;by\n");

        $this->assertSame(['name', 'since', 'by'], $parser->current());
    }

    public function testFromStreamRejectsNonResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvParserFactory()->fromStream('not-a-resource');
    }

    public function testFromFileRejectsMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvParserFactory()->fromFile(__DIR__ . '/../Fixtures/csv/missing.csv');
    }
}
