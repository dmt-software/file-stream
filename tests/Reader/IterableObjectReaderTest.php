<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader;

use DMT\FileStream\Reader\IterableObjectReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IterableObjectReader::class)]
final class IterableObjectReaderTest extends TestCase
{
    public function testReturnsObjectsFromIterable(): void
    {
        $first = new stdClass();
        $second = new stdClass();

        $reader = new IterableObjectReader(
            [$first, $second]
        );

        $this->assertSame(
            [$first, $second],
            iterator_to_array($reader->getResults())
        );
    }

    public function testPreservesIntegerKeys(): void
    {
        $first = new stdClass();
        $second = new stdClass();

        $reader = new IterableObjectReader(
            [
                10 => $first,
                20 => $second,
            ]
        );

        $this->assertSame(
            [
                10 => $first,
                20 => $second,
            ],
            iterator_to_array($reader->getResults())
        );
    }

    public function testPreservesIterationOrder(): void
    {
        $first = new stdClass();
        $second = new stdClass();
        $third = new stdClass();

        $reader = new IterableObjectReader(
            [
                20 => $first,
                10 => $second,
                30 => $third,
            ]
        );

        $this->assertSame(
            [
                20 => $first,
                10 => $second,
                30 => $third,
            ],
            iterator_to_array($reader->getResults())
        );
    }

    public function testAcceptsGenerator(): void
    {
        $first = new stdClass();
        $second = new stdClass();

        $objects = (static function () use ($first, $second) {
            yield 10 => $first;
            yield 20 => $second;
        })();

        $reader = new IterableObjectReader($objects);

        $this->assertSame(
            [
                10 => $first,
                20 => $second,
            ],
            iterator_to_array($reader->getResults())
        );
    }

    public function testReturnsNoResultsForEmptyIterable(): void
    {
        $reader = new IterableObjectReader([]);

        $this->assertSame(
            [],
            iterator_to_array($reader->getResults())
        );
    }
}
