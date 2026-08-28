<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Property;

use DMT\FileStream\Reader\Property\NamedPropertyStrategy;
use PHPUnit\Framework\TestCase;

final class NamedPropertyStrategyTest extends TestCase
{
    public function testAppliesPropertyNames(): void
    {
        $strategy = new NamedPropertyStrategy(['name', 'since', 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995, 'by' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testPadsMissingColumnsWithNull(): void
    {
        $strategy = new NamedPropertyStrategy(['name', 'since', 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995, 'by' => null],
            $strategy->apply(['PHP', 1995])
        );
    }

    public function testIgnoresAdditionalColumns(): void
    {
        $strategy = new NamedPropertyStrategy(['name', 'since']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testCombinesDuplicatePropertyNames(): void
    {
        $strategy = new NamedPropertyStrategy(['id', 'value', 'value']);

        $this->assertSame(
            ['id' => 1, 'value' => ['foo', 'bar']],
            $strategy->apply([1, 'foo', 'bar'])
        );
    }
}
