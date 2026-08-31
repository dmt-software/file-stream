<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Property;

use DMT\FileStream\Reader\Property\IndexColumnNamingStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexColumnNamingStrategy::class)]
final class IndexColumnNamingStrategyTest extends TestCase
{
    public function testMapsSelectedColumnsToPropertyNames(): void
    {
        $strategy = new IndexColumnNamingStrategy([0 => 'name', 2 => 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'by' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testMappingOrderIsBasedOnColumnIndex(): void
    {
        $strategy = new IndexColumnNamingStrategy([2 => 'by', 0 => 'name', 1 => 'since']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995, 'by' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testMissingSelectedColumnIsFilledWithNull(): void
    {
        $strategy = new IndexColumnNamingStrategy([0 => 'name', 2 => 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'by' => null],
            $strategy->apply(['PHP', 1995])
        );
    }

    public function testIgnoresColumnsThatAreNotMapped(): void
    {
        $strategy = new IndexColumnNamingStrategy([1 => 'since']);

        $this->assertSame(
            ['since' => 1995],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testCombinesDuplicatePropertyNames(): void
    {
        $strategy = new IndexColumnNamingStrategy([0 => 'value', 2 => 'value']);

        $this->assertSame(
            ['value' => ['foo', 'bar']],
            $strategy->apply(['foo', 'ignored', 'bar'])
        );
    }
}
