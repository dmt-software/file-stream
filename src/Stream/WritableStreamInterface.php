<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;

interface WritableStreamInterface
{
    /**
     * Check if the stream is writable.
     */
    public function isWritable(): bool;

    /**
     * Write to the stream.
     *
     * @throws WriterException When the stream cannot be written.
     */
    public function write(string $data): void;
}
