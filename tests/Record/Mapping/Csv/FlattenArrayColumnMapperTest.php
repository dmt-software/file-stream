<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\Csv\FlattenArrayColumnMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(FlattenArrayColumnMapper::class)]
final class FlattenArrayColumnMapperTest extends TestCase
{
    public function testMapScalarValues(): void
    {
        $mapper = new FlattenArrayColumnMapper();

        $this->assertSame(
            ['John', 42, true, 12.5, null],
            $mapper->map([
                'name' => 'John',
                'age' => 42,
                'active' => true,
                'score' => 12.5,
                'empty' => null,
            ])
        );
    }

    public function testFlattenArrayValueToFirstValue(): void
    {
        $mapper = new FlattenArrayColumnMapper();

        $this->assertSame(
            ['John', 'Doe'],
            $mapper->map([
                'firstName' => ['John', 'Johnny'],
                'lastName' => ['Doe', 'Smith'],
            ])
        );
    }

    public function testEmptyArrayValueBecomesNull(): void
    {
        $mapper = new FlattenArrayColumnMapper();

        $this->assertSame([null],
            $mapper->map(['value' => []])
        );
    }

    public function testNonScalarValuesBecomeNull(): void
    {
        $mapper = new FlattenArrayColumnMapper();

        $this->assertSame(
            [null, null],
            $mapper->map([
                'object' => new stdClass(),
                'resource' => fopen('php://memory', 'r'),
            ])
        );
    }

    public function testColumnCountIsDeterminedByFirstMappedRecord(): void
    {
        $mapper = new FlattenArrayColumnMapper();

        $mapper->map(['first' => 'A', 'second' => 'B', 'third' => 'C']);

        $this->assertSame(
            ['D', 'E', null],
            $mapper->map(['first' => 'D', 'second' => 'E'])
        );
    }

    public function testAdditionalValuesAreIgnoredAfterColumnCountIsDefined(): void
    {
        $mapper = new FlattenArrayColumnMapper();

        $mapper->map([
            'first' => 'A',
            'second' => 'B',
        ]);

        $this->assertSame(
            ['C', 'D'],
            $mapper->map(['first' => 'C', 'second' => 'D', 'third' => 'ignored'])
        );
    }

    public function testExplicitColumnCountPadsValues(): void
    {
        $mapper = new FlattenArrayColumnMapper(columnCount: 3);

        $this->assertSame(
            ['A', null, null],
            $mapper->map(['first' => 'A'])
        );
    }

    public function testExplicitColumnCountTruncatesValues(): void
    {
        $mapper = new FlattenArrayColumnMapper(columnCount: 2);

        $this->assertSame(
            ['A', 'B',],
            $mapper->map(['first' => 'A', 'second' => 'B', 'third' => 'C'])
        );
    }
}
