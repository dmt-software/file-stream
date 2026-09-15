<?php

declare(strict_types=1);

namespace DMT\FileStream\Record;

use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\SerializedWriterInterface;

/**
 * Writes serialized records to a writable stream.
 *
 * Each value is written sequentially to the configured output stream. After
 * all records have been written, the stream is flushed and closed.
 */
final readonly class RecordWriter implements SerializedWriterInterface
{
    public function __construct(private WritableStreamInterface $output)
    {
    }

    /**
     * @param iterable<int, string> $values
     */
    public function write(iterable $values): void
    {
        foreach ($values as $value) {
            $this->output->write($value);
        }

        $this->output->flush();
        $this->output->close();
    }
}
