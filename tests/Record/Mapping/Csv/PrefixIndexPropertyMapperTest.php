<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\Csv\PrefixIndexPropertyMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrefixIndexPropertyMapper::class)]
class PrefixIndexPropertyMapperTest extends TestCase
{
    public function testMapColumnsToPrefixedNamedProperties(): void
    {
        $mapper = new PrefixIndexPropertyMapper();

        $this->assertSame(
            [
                'column1' => 'first',
                'column2' => 'second',
                'column3' => 'third',
            ],
            $mapper->map(['first', 'second', 'third'])
        );
    }

    public function testMapColumnsToPrefixedCustomNamedProperties(): void
    {
        $mapper = new PrefixIndexPropertyMapper('prop_');

        $this->assertSame(
            [
                'prop_1' => 'first',
                'prop_2' => 'second',
                'prop_3' => 'third',
            ],
            $mapper->map(['first', 'second', 'third'])
        );
    }

    public function testReusePropertyNamesFromFirstMappedRecord(): void
    {
        $mapper = new PrefixIndexPropertyMapper();

        $mapper->map(['first','second']);

        $this->assertSame(
            [
                'column1' => 'first',
                'column2' => 'second',
            ],
            $mapper->map(['first', 'second', 'ignored'])
        );
    }
}
