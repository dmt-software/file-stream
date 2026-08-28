<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Property;

use DMT\FileStream\Reader\Property\NamedPropertiesStrategy;
use PHPUnit\Framework\TestCase;

final class NamedPropertiesStrategyTest extends TestCase
{
    public function testAppliesPropertyNames(): void
    {
        $strategy = new NamedPropertiesStrategy(['name', 'since', 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995, 'by' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testPadsMissingColumnsWithNull(): void
    {
        $strategy = new NamedPropertiesStrategy(['name', 'since', 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995, 'by' => null],
            $strategy->apply(['PHP', 1995])
        );
    }

    public function testIgnoresAdditionalColumns(): void
    {
        $strategy = new NamedPropertiesStrategy(['name', 'since']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testCombinesDuplicatePropertyNames(): void
    {
        $strategy = new NamedPropertiesStrategy(['id', 'value', 'value']);

        $this->assertSame(
            ['id' => 1, 'value' => ['foo', 'bar']],
            $strategy->apply([1, 'foo', 'bar'])
        );
    }
}
