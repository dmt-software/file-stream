<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Property;

use DMT\FileStream\Reader\Property\PrefixIndexNamingStrategy;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PrefixIndexNamingStrategy::class)]
final class PrefixIndexNamingStrategyTest extends TestCase
{
    public function testNamesColumnsByIndex(): void
    {
        $strategy = new PrefixIndexNamingStrategy();

        $this->assertSame(
            ['column0' => 'PHP', 'column1' => 1995, 'column2' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testUsesCustomPrefix(): void
    {
        $strategy = new PrefixIndexNamingStrategy('property');

        $this->assertSame(
            ['property0' => 'PHP', 'property1' => 1995, 'property2' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testUsesColumnCountFromFirstApply(): void
    {
        $strategy = new PrefixIndexNamingStrategy();
        $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf']);

        $this->assertSame(
            ['column0' => 'C#', 'column1' => 2000, 'column2' => null],
            $strategy->apply(['C#', 2000])
        );
    }
}
