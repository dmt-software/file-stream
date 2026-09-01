<?php

declare(strict_types=1);

namespace DMT\Test\FileStream;

use DMT\FileStream\Reader\IterableObjectReader;
use DMT\FileStream\ReadStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ReadStatement::class)]
final class ReadStatementTest extends TestCase
{
    public function testReturnsReaderResults(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second])
        );

        $this->assertSame(
            [$first, $second],
            iterator_to_array($statement->execute())
        );
    }

    public function testFiltersResults(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');
        $third = $this->object('third');

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second, $third])
        );

        $statement->filter(
            fn (stdClass $object, int $key): bool => $object->name !== 'second'
        );

        $this->assertSame(
            [0 => $first, 2 => $third],
            iterator_to_array($statement->execute())
        );
    }

    public function testAppliesOffsetAfterFiltering(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');
        $third = $this->object('third');

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second, $third])
        );

        $statement->filter(
            fn (stdClass $object, int $key): bool => $object->name !== 'first'
        );
        $statement->limit(1);

        $this->assertSame(
            [2 => $third],
            iterator_to_array($statement->execute())
        );
    }

    public function testAppliesLimitAfterFiltering(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');
        $third = $this->object('third');

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second, $third])
        );

        $statement->filter(
            fn (stdClass $object, int $key): bool => $object->name !== 'first'
        );
        $statement->limit(0, 1);

        $this->assertSame(
            [1 => $second],
            iterator_to_array($statement->execute())
        );
    }

    public function testAppliesModifierToReturnedResults(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second])
        );

        $statement->modify(
            function (stdClass $object, int $key): stdClass {
                $object->key = $key;

                return $object;
            }
        );

        $results = iterator_to_array($statement->execute());

        $this->assertSame(0, $results[0]->key);
        $this->assertSame(1, $results[1]->key);
    }

    public function testModifierIsOnlyAppliedAfterFilterAndLimit(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');
        $third = $this->object('third');
        $modified = [];

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second, $third])
        );

        $statement->filter(
            fn (stdClass $object, int $key): bool => $object->name !== 'first'
        );
        $statement->limit(0, 1);
        $statement->modify(
            function (stdClass $object, int $key) use (&$modified): stdClass {
                $modified[] = $key;

                return $object;
            }
        );

        iterator_to_array($statement->execute());

        $this->assertSame([1], $modified);
    }

    public function testAppliesMultipleFilters(): void
    {
        $first = $this->object('first');
        $second = $this->object('second');
        $third = $this->object('third');

        $statement = new ReadStatement(
            new IterableObjectReader([$first, $second, $third])
        );

        $statement->filter(
            fn (stdClass $object, int $key): bool => $object->name !== 'first'
        );
        $statement->filter(
            fn (stdClass $object, int $key): bool => $object->name !== 'third'
        );

        $this->assertSame(
            [1 => $second],
            iterator_to_array($statement->execute())
        );
    }

    public function testAppliesMultipleModifiersInOrder(): void
    {
        $object = $this->object('php');

        $statement = new ReadStatement(
            new IterableObjectReader([$object])
        );

        $statement->modify(
            function (stdClass $object, int $key): stdClass {
                $object->name = strtoupper($object->name);

                return $object;
            }
        );
        $statement->modify(
            function (stdClass $object, int $key): stdClass {
                $object->name .= '!';

                return $object;
            }
        );

        $results = iterator_to_array($statement->execute());

        $this->assertSame('PHP!', $results[0]->name);
    }

    private function object(string $name): stdClass
    {
        $object = new stdClass();
        $object->name = $name;

        return $object;
    }
}
