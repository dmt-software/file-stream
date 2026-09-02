<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader;

use ArrayIterator;
use DMT\FileStream\Reader\StreamObjectReader;
use DMT\FileStream\Serialization\DeserializerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(StreamObjectReader::class)]
final class StreamObjectReaderTest extends TestCase
{
    public function testDeserializesIteratorValues(): void
    {
        $first = new stdClass();
        $second = new stdClass();

        $deserializer = $this->createMock(DeserializerInterface::class);
        $deserializer
            ->expects($this->exactly(2))
            ->method('deserialize')
            ->willReturnMap([
                ['first', $first],
                ['second', $second],
            ]);

        $reader = new StreamObjectReader(
            new ArrayIterator([
                'first',
                'second',
            ]),
            $deserializer
        );

        $this->assertSame(
            [$first, $second],
            iterator_to_array($reader->getResults())
        );
    }

    public function testPreservesIteratorKeys(): void
    {
        $first = new stdClass();
        $second = new stdClass();

        $deserializer = $this->createStub(DeserializerInterface::class);
        $deserializer
            ->method('deserialize')
            ->willReturnOnConsecutiveCalls($first, $second);

        $reader = new StreamObjectReader(
            new ArrayIterator([
                10 => 'first',
                20 => 'second',
            ]),
            $deserializer
        );

        $this->assertSame(
            [
                10 => $first,
                20 => $second,
            ],
            iterator_to_array($reader->getResults())
        );
    }

    public function testPassesSerializedValueToDeserializer(): void
    {
        $object = new stdClass();

        $deserializer = $this->createMock(DeserializerInterface::class);
        $deserializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('serialized object')
            ->willReturn($object);

        $reader = new StreamObjectReader(
            new ArrayIterator([
                'serialized object',
            ]),
            $deserializer
        );

        $this->assertSame(
            [0 => $object],
            iterator_to_array($reader->getResults())
        );
    }

    public function testReturnsNoResultsForEmptyIterator(): void
    {
        $deserializer = $this->createMock(DeserializerInterface::class);
        $deserializer
            ->expects($this->never())
            ->method('deserialize');

        $reader = new StreamObjectReader(
            new ArrayIterator([]),
            $deserializer
        );

        $this->assertSame(
            [],
            iterator_to_array($reader->getResults())
        );
    }
}
