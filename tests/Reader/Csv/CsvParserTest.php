<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Csv;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;
use PHPUnit\Framework\TestCase;

final class CsvParserTest extends TestCase
{
    public const string FILE = __DIR__ . '/../../Fixtures/csv/programming.csv';

    public function testReadsRows(): void
    {
        $parser = new CsvParser(fopen(self::FILE, 'r'));

        $this->assertSame(
            ['name', 'since', 'by'],
            $parser->current()
        );

        $parser->next();

        $this->assertSame(
            ['Javascript', '1995', 'Brendan Eich'],
            $parser->current()
        );

        $this->assertSame(0, $parser->key());
    }

    public function testIteratesOverAllRows(): void
    {
        $parser = new CsvParser(fopen(self::FILE, 'r'));

        $this->assertCount(4, iterator_to_array($parser));
    }

    public function testReturnsFalseForEmptyStream(): void
    {
        $parser = new CsvParser(fopen('php://memory', 'r+b'));

        $this->assertFalse($parser->current());
    }

    public function testCannotRewindAfterAdvancing(): void
    {
        $this->expectException(ReaderException::class);

        $parser = new CsvParser(fopen(self::FILE, 'r'));
        $parser->next();
        $parser->rewind();
    }
}
