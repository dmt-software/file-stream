<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\Csv\PredefinedNamedColumnMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PredefinedNamedColumnMapper::class)]
final class PredefinedNamedColumnMapperTest extends TestCase
{
    public function testMapNamedPropertiesToColumns(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['firstName', 'lastName']);

        $this->assertSame(
            ['John', 'Doe'],
            $mapper->map([
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
        );
    }

    public function testColumnOrderFollowsConfiguredNames(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['lastName', 'firstName']);

        $this->assertSame(
            ['Doe', 'John'],
            $mapper->map([
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
        );
    }

    public function testMissingPropertyBecomesNull(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['firstName', 'lastName']);

        $this->assertSame(
            ['John', null],
            $mapper->map(['firstName' => 'John'])
        );
    }

    public function testAdditionalPropertiesAreIgnored(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['firstName',]);

        $this->assertSame(
            ['John'],
            $mapper->map(['firstName' => 'John', 'lastName' => 'Doe'])
        );
    }

    public function testDuplicateColumnNamesUseSubsequentArrayValues(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['name', 'name', 'name']);

        $this->assertSame(
            ['John', 'Johnny', 'Jonathan'],
            $mapper->map(['name' => ['John', 'Johnny', 'Jonathan']])
        );
    }

    public function testDuplicateColumnNamesFillMissingArrayValuesWithNull(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['name', 'name', 'name']);

        $this->assertSame(
            ['John', null, null],
            $mapper->map(['name' => ['John']])
        );
    }

    public function testDuplicateScalarPropertyOnlyUsesFirstOccurrence(): void
    {
        $mapper = new PredefinedNamedColumnMapper(['name', 'name']);

        $this->assertSame(
            ['John', null],
            $mapper->map(['name' => 'John'])
        );
    }

    public function testEmptyColumnNamesReturnEmptyColumns(): void
    {
        $mapper = new PredefinedNamedColumnMapper();

        $this->assertSame(
            [],
            $mapper->map(['name' => 'John'])
        );
    }
}
