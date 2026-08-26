<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;
use DMT\FileStream\Reader\Csv\Header\NumberedColumnsHeader;
use PHPUnit\Framework\TestCase;

final class NumberedColumnsHeaderTest extends TestCase
{
    public const string FILE_PATH = __DIR__ . '/../../../Fixtures/csv/';

    public function testCreatesNumberedColumns(): void
    {
        $parser = new CsvParser(fopen(self::FILE_PATH . 'headerless.csv', 'r'));
        $header = new NumberedColumnsHeader($parser);

        $this->assertSame(
            ['column_0', 'column_1', 'column_2'],
            $header->getHeader()
        );
        $this->assertSame(0, $parser->key());

        $this->assertSame(
            ['Javascript', '1995', 'Brendan Eich'],
            $parser->current()
        );
    }

    public function testOverridesColumnHeader(): void
    {
        $parser = new CsvParser(fopen(self::FILE_PATH . 'programming.csv', 'r'));
        $header = new NumberedColumnsHeader($parser, overridesColumnHeader: true);

        $this->assertSame(
            ['column_0', 'column_1', 'column_2'],
            $header->getHeader()
        );
        $this->assertSame(0, $parser->key());

        $this->assertSame(
            ['Javascript', '1995', 'Brendan Eich'],
            $parser->current()
        );
    }

    public function testThrowsForEmptyCsv(): void
    {
        $this->expectException(NotFoundException::class);

        $header = new NumberedColumnsHeader(new CsvParser(fopen('php://memory', 'r+b')));
        $header->getHeader();
    }

    public function testThrowsWhenParserAlreadyAdvanced(): void
    {
        $parser = new CsvParser(fopen(self::FILE_PATH . 'programming.csv', 'r'));
        $parser->next();

        $this->expectException(ReaderException::class);

        $header = new NumberedColumnsHeader($parser);
        $header->getHeader();
    }
}
