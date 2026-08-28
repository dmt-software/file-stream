<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Property;

use DMT\FileStream\Reader\Property\FirstLineNamingStrategy;
use PHPUnit\Framework\TestCase;

final class FirstLineNamingStrategyTest extends TestCase
{
    public function testUsesFirstLineAsPropertyNames(): void
    {
        $strategy = new FirstLineNamingStrategy();

        $this->assertSame(
            ['name' => 'name', 'since' => 'since', 'by' => 'by'],
            $strategy->apply(['name', 'since', 'by'])
        );
    }

    public function testUsesFirstLineForSubsequentRows(): void
    {
        $strategy = new FirstLineNamingStrategy();
        $strategy->apply(['name', 'since', 'by']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995, 'by' => 'Rasmus Lerdorf'],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }

    public function testFirstLineDeterminesColumnCount(): void
    {
        $strategy = new FirstLineNamingStrategy();
        $strategy->apply(['name', 'since']);

        $this->assertSame(
            ['name' => 'PHP', 'since' => 1995],
            $strategy->apply(['PHP', 1995, 'Rasmus Lerdorf'])
        );
    }
}
