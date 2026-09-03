<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Column;

use DMT\FileStream\Writer\Column\NamedColumnStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamedColumnStrategy::class)]
final class NamedColumnStrategyTest extends TestCase
{
    public function testReturnsColumnsInConfiguredOrder(): void
    {
        $strategy = new NamedColumnStrategy(['name', 'since']);

        $this->assertSame(
            ['PHP', 1995],
            $strategy->apply(['since' => 1995, 'name' => 'PHP'])
        );
    }

    public function testReturnsNullForMissingColumn(): void
    {
        $strategy = new NamedColumnStrategy(['name', 'since']);

        $this->assertSame(
            ['PHP', null],
            $strategy->apply(['name' => 'PHP'])
        );
    }

    public function testIgnoresPropertiesWithoutConfiguredColumn(): void
    {
        $strategy = new NamedColumnStrategy(['name']);

        $this->assertSame(
            ['PHP'],
            $strategy->apply(['name' => 'PHP', 'since' => 1995])
        );
    }

    public function testUsesArrayValuesForRepeatedColumns(): void
    {
        $strategy = new NamedColumnStrategy(['name', 'tag', 'tag']);

        $this->assertSame(
            ['PHP', 'language', 'backend'],
            $strategy->apply([
                'name' => 'PHP',
                'tag' => [
                    'language',
                    'backend',
                ],
            ])
        );
    }

    public function testPadsRepeatedColumnsWithNull(): void
    {
        $strategy = new NamedColumnStrategy(['name', 'tag', 'tag']);

        $this->assertSame(
            ['PHP', 'language', null],
            $strategy->apply([
                'name' => 'PHP',
                'tag' => [
                    'language',
                ],
            ])
        );
    }

    public function testIgnoresExcessValuesForRepeatedColumns(): void
    {
        $strategy = new NamedColumnStrategy(['name', 'tag', 'tag']);

        $this->assertSame(
            ['PHP', 'language', 'backend'],
            $strategy->apply([
                'name' => 'PHP',
                'tag' => [
                    'language',
                    'backend',
                    'web',
                ],
            ])
        );
    }

    public function testRepeatedScalarColumnOnlyUsesValueOnce(): void
    {
        $strategy = new NamedColumnStrategy(['name', 'tag', 'tag']);

        $this->assertSame(
            ['PHP', 'language', null],
            $strategy->apply([
                'name' => 'PHP',
                'tag' => 'language',
            ])
        );
    }

    public function testRepeatedColumnsCanBeSeparated(): void
    {
        $strategy = new NamedColumnStrategy(['tag', 'name', 'tag']);

        $this->assertSame(
            ['language', 'PHP', 'backend'],
            $strategy->apply([
                'name' => 'PHP',
                'tag' => [
                    'language',
                    'backend',
                ],
            ])
        );
    }
}
