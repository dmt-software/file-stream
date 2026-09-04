<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Column;

use DMT\FileStream\Writer\Column\FlattenArrayColumnStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(FlattenArrayColumnStrategy::class)]
final class FlattenArrayColumnStrategyTest extends TestCase
{
    public function testReturnsScalarProperties(): void
    {
        $strategy = new FlattenArrayColumnStrategy();

        $this->assertSame(
            ['PHP', 1995, true],
            $strategy->apply(['PHP', 1995, true])
        );
    }

    public function testUsesFirstValueOfArrayProperty(): void
    {
        $strategy = new FlattenArrayColumnStrategy();

        $this->assertSame(
            ['PHP', 'language'],
            $strategy->apply([
                'PHP',
                [
                    'language',
                    'backend',
                ],
            ])
        );
    }

    public function testReplacesNonScalarValueWithNull(): void
    {
        $strategy = new FlattenArrayColumnStrategy();

        $this->assertSame(
            ['PHP', null],
            $strategy->apply(['PHP', new stdClass()])
        );
    }

    public function testFirstRecordDeterminesColumnCount(): void
    {
        $strategy = new FlattenArrayColumnStrategy();
        $strategy->apply(['PHP', 1995]);

        $result = $strategy->apply(['JavaScript']);

        $this->assertCount(2, $result);
        $this->assertSame(['JavaScript', null], $result);
    }

    public function testTruncatesColumnsToDeterminedCount(): void
    {
        $strategy = new FlattenArrayColumnStrategy();
        $strategy->apply(['PHP', 1995]);

        $result = $strategy->apply(['JavaScript', 1995, 'Brendan Eich',]);

        $this->assertCount(2, $result);
        $this->assertSame(['JavaScript', 1995], $result);
    }

    public function testConfiguredColumnCountOverridesDeterminedCount(): void
    {
        $strategy = new FlattenArrayColumnStrategy(columnCount: 3);

        $result = $strategy->apply(['PHP', 1995]);

        $this->assertCount(3, $result);
        $this->assertSame(['PHP', 1995, null], $result);
    }

    public function testNullValueRemainsNull(): void
    {
        $strategy = new FlattenArrayColumnStrategy();

        $this->assertSame(
            ['PHP', null],
            $strategy->apply(['PHP', null])
        );
    }

    public function testEmptyArrayBecomesNull(): void
    {
        $strategy = new FlattenArrayColumnStrategy();

        $this->assertSame(
            ['PHP', null],
            $strategy->apply(['PHP', []])
        );
    }
}
