<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Record;

use DMT\FileStream\Record\Boundary\RecordBoundaryInterface;
use DMT\FileStream\Record\RecordIterable;
use DMT\FileStream\Stream\ResourceReaderStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordIterable::class)]
final class RecordIterableTest extends TestCase
{
    public function testIterateCompleteRecords(): void
    {
        $iterable = new RecordIterable(
            $this->createStream("first\nsecond\n"),
            $this->createLineBoundary()
        );

        $this->assertSame(
            [0 => "first\n", 1 => "second\n"],
            iterator_to_array($iterable->getIterator())
        );
    }

    public function testAccumulateInputUntilBoundaryIsReached(): void
    {
        $stream = $this->createStream("first\ncontinued\nsecond\n");
        $boundary = new class implements RecordBoundaryInterface {
            public function isBoundary(string $data): bool
            {
                return substr_count($data, "\n") === 2;
            }
        };

        $iterable = new RecordIterable($stream, $boundary);

        $this->assertSame(
            [0 => "first\ncontinued\n", 1 => "second\n"],
            iterator_to_array($iterable->getIterator())
        );
    }

    public function testYieldIncompleteRecordAtEndOfStream(): void
    {
        $iterable = new RecordIterable(
            $this->createStream("first\nsecond"),
            $this->createLineBoundary()
        );

        $this->assertSame(
            [0 => "first\n", 1 => 'second'],
            iterator_to_array($iterable->getIterator())
        );
    }

    public function testYieldSingleIncompleteRecord(): void
    {
        $iterable = new RecordIterable(
            $this->createStream('record'),
            $this->createLineBoundary()
        );

        $this->assertSame(
            [0 => 'record',],
            iterator_to_array($iterable->getIterator())
        );
    }

    public function testEmptyStreamYieldsNoRecords(): void
    {
        $iterable = new RecordIterable(
            $this->createStream(''),
            $this->createLineBoundary()
        );

        $this->assertSame([], iterator_to_array($iterable->getIterator()));
    }

    public function testKeysAreSequential(): void
    {
        $iterable = new RecordIterable(
            $this->createStream("one\ntwo\nthree\n"),
            $this->createLineBoundary()
        );

        $this->assertSame(
            [0, 1, 2],
            array_keys(iterator_to_array($iterable->getIterator()))
        );
    }

    private function createStream(string $contents): ResourceReaderStream
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, $contents);
        rewind($resource);

        return new ResourceReaderStream($resource);
    }

    private function createLineBoundary(): RecordBoundaryInterface
    {
        return new class implements RecordBoundaryInterface {
            public function isBoundary(string $data): bool
            {
                return str_ends_with($data, "\n");
            }
        };
    }
}
