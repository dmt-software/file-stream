<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use DMT\FileStream\Stream\Buffer\OutputBuffer;

/**
 * Provides a buffered writable stream for serialized JSON values.
 *
 * Values written through write() are separated by commas. The first value is
 * written unchanged, while every subsequent value is prefixed with a comma.
 *
 * Direct writes to the stream returned by getStream() bypass value separation
 * and can be used for structural JSON content such as array delimiters.
 *
 * @implements WritableStreamInterface<resource>
 */
final class JsonWriterStream implements WritableStreamInterface
{
    private readonly OutputBuffer $buffer;

    private bool $closed = false;

    private bool $valueWritten = false;

    public function __construct(
        private readonly mixed $stream,
        int $bufferSize = 65536,
    ) {
        StreamValidator::writable($stream);

        $this->buffer = new OutputBuffer($stream, $bufferSize);
    }

    public function getStream(): mixed
    {
        return $this->buffer->getStream();
    }

    public function isWritable(): bool
    {
        return !$this->closed && is_resource($this->stream);
    }

    public function write(string $data): void
    {
        if (!$this->isWritable()) {
            throw WriterException::unwritable();
        }

        if ($this->valueWritten) {
            $this->buffer->write(',');
        }

        $this->buffer->write($data);

        $this->valueWritten = true;
    }

    public function flush(): void
    {
        if (!$this->isWritable()) {
            throw WriterException::unwritable();
        }

        $this->buffer->flush();
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        if (!is_resource($this->stream)) {
            $this->closed = true;

            return;
        }

        $this->buffer->close();

        fclose($this->stream);

        $this->closed = true;
    }
}
