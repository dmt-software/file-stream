<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;

interface ReadableStreamInterface
{
    /**
     * Check if the stream is readable.
     */
    public function isReadable(): bool;

    /**
     * Read from the stream.
     *
     * @throws ReaderException When the stream cannot be read.
     */
    public function read(?int $length = null): string;

    /**
     * Check if the stream is seekable.
     */
    public function isSeekable(): bool;

    /**
     * Rewind the stream to the beginning.
     *
     * @throws ReaderException When the stream cannot be seeked.
     */
    public function rewind(): void;

    /**
     * Check if the file pointer is at the end of the stream.
     */
    public function endOfFile(): bool;
}
