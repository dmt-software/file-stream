<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use DMT\FileStream\Stream\Buffer\OutputBuffer;
use InvalidArgumentException;

/**
 * Provides a buffered writable stream backed by a PHP stream resource.
 *
 * Data is written to an in-memory buffer and periodically flushed to the
 * underlying output resource. The active buffer resource is exposed through
 * getStream().
 *
 * @implements WritableStreamInterface<resource>
 */
final class ResourceWriterStream implements WritableStreamInterface
{
    private readonly OutputBuffer $buffer;

    private bool $closed = false;

    /**
     * @param resource $stream
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly mixed $stream,
        int $bufferSize = 65536,
    ) {
        StreamValidator::writable($stream);

        $this->buffer = new OutputBuffer($stream, $bufferSize);
    }

    /**
     * @return resource
     */
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

        $this->buffer->write($data);
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

        $this->closed = fclose($this->stream);
    }
}
