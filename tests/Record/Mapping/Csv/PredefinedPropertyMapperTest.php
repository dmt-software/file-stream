<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\Csv\PredefinedPropertyMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PredefinedPropertyMapper::class)]
final class PredefinedPropertyMapperTest extends TestCase
{
    public function testMapValuesToPredefinedPropertyNames(): void
    {
        $mapper = new PredefinedPropertyMapper(['firstName', 'lastName']);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            $mapper->map(['John', 'Doe'])
        );
    }

    public function testMissingValuesAreFilledWithNull(): void
    {
        $mapper = new PredefinedPropertyMapper(['firstName', 'lastName', 'age']);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => null,
                'age' => null,
            ],
            $mapper->map(['John'])
        );
    }

    public function testAdditionalValuesAreIgnored(): void
    {
        $mapper = new PredefinedPropertyMapper(['firstName', 'lastName']);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            $mapper->map(['John', 'Doe', 'ignored'])
        );
    }

    public function testDuplicatePropertyNamesAreGroupedIntoArray(): void
    {
        $mapper = new PredefinedPropertyMapper(['name', 'name', 'age']);

        $this->assertSame(
            [
                'name' => ['John', 'Doe'],
                'age' => '42',
            ],
            $mapper->map(['John', 'Doe', '42'])
        );
    }

    public function testDuplicatePropertyNamesPreserveMissingValues(): void
    {
        $mapper = new PredefinedPropertyMapper(['name', 'name']);

        $this->assertSame(
            [
                'name' => ['John', null],
            ],
            $mapper->map(['John'])
        );
    }

    public function testPropertyNamesAreOrderedByIndex(): void
    {
        $mapper = new PredefinedPropertyMapper([
            1 => 'lastName',
            0 => 'firstName',
        ]);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            $mapper->map(['John', 'Doe'])
        );
    }

    public function testMapSparsePropertyIndexes(): void
    {
        $mapper = new PredefinedPropertyMapper([
            1 => 'firstName',
            3 => 'lastName',
        ]);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            $mapper->map(['ignored', 'John', 'ignored', 'Doe'])
        );
    }

    public function testMissingValueAtSparseIndexIsNull(): void
    {
        $mapper = new PredefinedPropertyMapper([
            1 => 'firstName',
            3 => 'lastName',
        ]);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => null,
            ],
            $mapper->map(['ignored', 'John'])
        );
    }
}
