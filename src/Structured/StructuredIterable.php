<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured;

use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Structured\Selector\SelectorInterface;
use Iterator;
use IteratorAggregate;

/**
 * Iterates over selected structures from a readable stream.
 *
 * The configured selector advances the stream to each matching structure.
 * Every selected structure is then yielded as a string for further processing,
 * such as deserialization by an object reader.
 *
 * @implements IteratorAggregate<int, string>
 */
final readonly class StructuredIterable implements IteratorAggregate
{
    public function __construct(
        private ReadableStreamInterface $stream,
        private SelectorInterface $selector,
    ) {
    }

    /**
     * @return Iterator<int, string>
     */
    public function getIterator(): Iterator
    {
        $key = 0;

        while ($this->selector->selectNext($this->stream)) {
            yield $key++ => $this->stream->current();
        }
    }
}
