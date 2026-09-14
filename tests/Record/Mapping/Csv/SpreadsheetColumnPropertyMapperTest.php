<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\Csv\SpreadsheetColumnPropertyMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpreadsheetColumnPropertyMapper::class)]
final class SpreadsheetColumnPropertyMapperTest extends TestCase
{
    public function testMapColumnsToSpreadsheetPropertyNames(): void
    {
        $mapper = new SpreadsheetColumnPropertyMapper();

        $this->assertSame(
            [
                'A' => 'first',
                'B' => 'second',
                'C' => 'third',
            ],
            $mapper->map(['first', 'second', 'third'])
        );
    }

    public function testGenerateNamesBeyondSingleLetterColumns(): void
    {
        $mapper = new SpreadsheetColumnPropertyMapper();

        $values = array_fill(0, 28, null);
        $values[25] = 'z';
        $values[26] = 'aa';
        $values[27] = 'ab';

        $result = $mapper->map($values);

        $this->assertSame('z', $result['Z']);
        $this->assertSame('aa', $result['AA']);
        $this->assertSame('ab', $result['AB']);
    }

    public function testGenerateTripleLetterColumnName(): void
    {
        $mapper = new SpreadsheetColumnPropertyMapper();

        $values = array_fill(0, 703, null);
        $values[702] = 'value';

        $result = $mapper->map($values);

        $this->assertSame('value', $result['AAA']);
    }

    public function testReusePropertyNamesFromFirstMappedRecord(): void
    {
        $mapper = new SpreadsheetColumnPropertyMapper();

        $mapper->map(['first', 'second', 'third']);

        $this->assertSame(
            [
                'A' => 'one',
                'B' => 'two',
                'C' => null,
            ],
            $mapper->map(['one', 'two'])
        );
    }

    public function testIgnoreAdditionalColumnsAfterFirstMappedRecord(): void
    {
        $mapper = new SpreadsheetColumnPropertyMapper();

        $mapper->map(['first', 'second']);

        $this->assertSame(
            [
                'A' => 'one',
                'B' => 'two',
            ],
            $mapper->map(['one', 'two', 'ignored'])
        );
    }
}
