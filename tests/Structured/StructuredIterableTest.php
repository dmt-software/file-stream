<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured;

use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Structured\Selector\SelectorInterface;
use DMT\FileStream\Structured\StructuredIterable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructuredIterable::class)]
final class StructuredIterableTest extends TestCase
{
    public function testYieldSelectedStructures(): void
    {
        $stream = $this->createMock(ReadableStreamInterface::class);
        $stream
            ->expects($this->exactly(2))
            ->method('current')
            ->willReturnOnConsecutiveCalls(
                '{"name":"first"}',
                '{"name":"second"}',
            );

        $selector = $this->createMock(SelectorInterface::class);
        $selector
            ->expects($this->exactly(3))
            ->method('selectNext')
            ->with($stream)
            ->willReturnOnConsecutiveCalls(true, true, false);


        $iterable = new StructuredIterable($stream, $selector);

        $this->assertSame(
            [
                0 => '{"name":"first"}',
                1 => '{"name":"second"}',
            ],
            iterator_to_array($iterable->getIterator())
        );
    }

    public function testEmptySelectionYieldsNoStructures(): void
    {
        $stream = $this->createMock(ReadableStreamInterface::class);
        $stream
            ->expects($this->never())
            ->method('current');

        $selector = $this->createMock(SelectorInterface::class);
        $selector
            ->expects($this->once())
            ->method('selectNext')
            ->with($stream)
            ->willReturn(false);


        $iterable = new StructuredIterable($stream, $selector);

        $this->assertSame([], iterator_to_array($iterable->getIterator()));
    }

    public function testKeysAreSequential(): void
    {
        $stream = $this->createMock(ReadableStreamInterface::class);
        $stream
            ->method('current')
            ->willReturnOnConsecutiveCalls(
                'first',
                'second',
                'third',
            );

        $selector = $this->createMock(SelectorInterface::class);
        $selector
            ->method('selectNext')
            ->willReturnOnConsecutiveCalls(true, true, true, false);


        $iterable = new StructuredIterable($stream, $selector);

        $this->assertSame(
            [0, 1, 2,],
            array_keys(iterator_to_array($iterable->getIterator()))
        );
    }
}
