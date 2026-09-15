<?php

declare(strict_types=1);

namespace DMT\Test\FileStream;

use ArrayIterator;
use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\ReadStatement;
use DMT\FileStream\Reader\ObjectReaderInterface;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ReadStatement::class)]
final class ReadStatementTest extends TestCase
{
    public function testExecuteReturnsReaderResults(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
                1 => (object)['value' => 2],
            ])
        );

        $results = iterator_to_array($statement->execute());

        $this->assertSame(
            [0 => 1, 1 => 2],
            array_map(
                static fn (stdClass $object): int => $object->value,
                $results
            )
        );
    }

    public function testApplyFilter(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
                1 => (object)['value' => 2],
                2 => (object)['value' => 3],
            ])
        );

        $statement->filter(
            new CallbackFilter(
                static fn (object $object, int $key): bool => $object->value >= 2
            )
        );

        $results = iterator_to_array($statement->execute());

        $this->assertSame(
            [1 => 2, 2 => 3],
            array_map(
                static fn (stdClass $object): int => $object->value,
                $results
            )
        );
    }

    public function testApplyCallableFilter(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
                1 => (object)['value' => 2],
                2 => (object)['value' => 3],
            ])
        );

        $statement->filter(
            static fn (object $object, int $key): bool => $object->value !== 2
        );

        $results = iterator_to_array($statement->execute());

        $this->assertSame(
            [0 => 1, 2 => 3],
            array_map(
                static fn (stdClass $object): int => $object->value,
                $results
            )
        );
    }

    public function testApplyFiltersInOrder(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
            ])
        );

        $calls = [];

        $statement
            ->filter(
                static function (object $object, int $key) use (&$calls): bool {
                    $calls[] = 'first';

                    return true;
                }
            )
            ->filter(
                static function (object $object, int $key) use (&$calls): bool {
                    $calls[] = 'second';

                    return true;
                }
            );

        iterator_to_array($statement->execute());

        $this->assertSame(['first', 'second'], $calls);
    }

    public function testApplyOffsetAfterFiltering(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
                1 => (object)['value' => 2],
                2 => (object)['value' => 3],
                3 => (object)['value' => 4],
            ])
        );

        $statement
            ->filter(
                static fn (object $object, int $key): bool => $object->value >= 2
            )
            ->limit(offset: 1);

        $results = iterator_to_array($statement->execute());

        $this->assertSame(
            [2 => 3, 3 => 4],
            array_map(
                static fn (stdClass $object): int => $object->value,
                $results
            )
        );
    }

    public function testApplyLimitAfterFiltering(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
                1 => (object)['value' => 2],
                2 => (object)['value' => 3],
                3 => (object)['value' => 4],
            ])
        );

        $statement
            ->filter(
                static fn (object $object, int $key): bool => $object->value >= 2
            )
            ->limit(limit: 2);

        $results = iterator_to_array($statement->execute());

        $this->assertCount(2, $results);
        $this->assertSame(
            [1 => 2, 2 => 3],
            array_map(
                static fn (stdClass $object): int => $object->value,
                $results
            )
        );
    }

    public function testApplyOffsetAndLimitAfterFiltering(): void
    {
        $statement = new ReadStatement(
            $this->createReader([
                0 => (object)['value' => 1],
                1 => (object)['value' => 2],
                2 => (object)['value' => 3],
                3 => (object)['value' => 4],
                4 => (object)['value' => 5],
            ])
        );

        $statement
            ->filter(
                static fn (object $object, int $key): bool => $object->value >= 2
            )
            ->limit(offset: 1, limit: 2);

        $results = iterator_to_array($statement->execute());

        $this->assertCount(2, $results);
        $this->assertSame(
            [2 => 3, 3 => 4],
            array_map(
                static fn (stdClass $object): int => $object->value,
                $results
            )
        );
    }

    /**
     * @param array<int, stdClass> $objects
     * @return ObjectReaderInterface<stdClass>
     */
    private function createReader(array $objects): ObjectReaderInterface
    {
        return new class($objects) implements ObjectReaderInterface {
            /**
             * @param array<int, stdClass> $objects
             */
            public function __construct(
                private readonly array $objects
            ) {
            }

            /**
             * @return Iterator<int, stdClass>
             */
            public function getResults(): Iterator
            {
                yield from new ArrayIterator($this->objects);
            }
        };
    }
}
