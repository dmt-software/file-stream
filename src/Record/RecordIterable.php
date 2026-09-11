<?php

declare(strict_types=1);

namespace DMT\FileStream\Record;

use DMT\FileStream\Record\Boundary\RecordBoundaryInterface;
use DMT\FileStream\SerializedIterableInterface;
use DMT\FileStream\Stream\ReadableStreamInterface;
use Iterator;

/**
 * Iterates over complete logical records from a readable stream.
 *
 * Physical stream input is consumed until the configured record boundary
 * determines that a complete record has been reached. Each completed record
 * is then yielded as a string for further processing, such as deserialization
 * by an object reader.
 *
 * @implements SerializedIterableInterface<int, string>
 */
final readonly class RecordIterable implements SerializedIterableInterface
{
    public function __construct(
        private ReadableStreamInterface $stream,
        private RecordBoundaryInterface $boundary,
    ) {
    }

    /**
     * @return Iterator<int, string>
     */
    public function getIterator(): Iterator
    {
        $key = 0;
        $record = '';

        while ($this->stream->next()) {
            $record .= $this->stream->current();

            if (!$this->boundary->isBoundary($record)) {
                continue;
            }

            yield $key++ => $record;

            $record = '';
        }

        if ($record !== '') {
            yield $key => $record;
        }
    }
}
