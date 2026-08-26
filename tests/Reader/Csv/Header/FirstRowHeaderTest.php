<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;
use DMT\FileStream\Reader\Csv\Header\FirstRowHeader;
use PHPUnit\Framework\TestCase;

final class FirstRowHeaderTest extends TestCase
{
    public const string FILE = __DIR__ . '/../../../Fixtures/csv/programming.csv';

    public function testUsesFirstRowAsHeader(): void
    {
        $parser = new CsvParser(fopen(self::FILE, 'r'));
        $header = new FirstRowHeader($parser);

        $this->assertSame(['name', 'since', 'by'], $header->getHeader());
        $this->assertSame(0, $parser->key());
    }

    public function testCachesHeader(): void
    {
        $header = new FirstRowHeader(new CsvParser(fopen(self::FILE, 'r')));

        $this->assertSame($header->getHeader(), $header->getHeader());
    }

    public function testThrowsForEmptyCsv(): void
    {
        $this->expectException(NotFoundException::class);

        $header = new FirstRowHeader(new CsvParser(fopen('php://memory', 'r+b')));
        $header->getHeader();
    }

    public function testThrowsWhenParserAlreadyAdvanced(): void
    {
        $parser = new CsvParser(fopen(self::FILE, 'r'));;
        $parser->next();

        $this->expectException(ReaderException::class);

        $header = new FirstRowHeader($parser);
        $header->getHeader();
    }
}
