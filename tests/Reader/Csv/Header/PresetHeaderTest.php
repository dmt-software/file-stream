<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;
use DMT\FileStream\Reader\Csv\Header\PresetHeader;
use PHPUnit\Framework\TestCase;

final class PresetHeaderTest extends TestCase
{
    public const string FILE_PATH = __DIR__ . '/../../../Fixtures/csv/';

    public function testReturnsPresetHeader(): void
    {
        $parser = new CsvParser(fopen(self::FILE_PATH . 'headerless.csv', 'r'));

        $columns = ['name', 'since', 'by'];
        $header = new PresetHeader($parser, $columns);

        $this->assertSame($columns, $header->getHeader());
        $this->assertSame(0, $parser->key());

        $this->assertSame(
            ['Javascript', '1995', 'Brendan Eich'],
            $parser->current()
        );
    }

    public function testOverridesColumnHeader(): void
    {
        $parser = new CsvParser(fopen(self::FILE_PATH . 'programming.csv', 'r'));

        $columns = ['name', 'since', 'author'];
        $header = new PresetHeader($parser, $columns, true);

        $this->assertSame($columns, $header->getHeader());
        $this->assertSame(0, $parser->key());

        $this->assertSame(
            ['Javascript', '1995', 'Brendan Eich'],
            $parser->current()
        );
    }

    public function testThrowsWhenParserAlreadyAdvanced(): void
    {
        $parser = new CsvParser(fopen(self::FILE_PATH . 'programming.csv', 'r'));
        $parser->next();

        $this->expectException(ReaderException::class);

        $header = new PresetHeader($parser, ['name', 'since', 'author'], true);
        $header->getHeader();
    }
}
