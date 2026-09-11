<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;

/**
 * Provides a buffered writable stream backed by a PHP stream resource.
 *
 * Data is written to an internal temporary stream and copied to the underlying
 * output resource when flushed. This allows multiple writes to be combined
 * before accessing the destination stream.
 *
 * @implements WritableStreamInterface<resource>
 */
final class ResourceWriterStream implements WritableStreamInterface
{
    /** @var resource */
    private readonly mixed $resource;

    /** @var resource */
    private readonly mixed $stream;

    private bool $closed = false;

    /**
     * @param resource $resource
     */
    public function __construct(mixed $resource)
    {
        StreamValidator::writable($resource);

        $stream = fopen('php://memory', 'w+');

        if ($stream === false) {
            throw WriterException::failure();
        }

        $this->resource = $resource;
        $this->stream = $stream;
    }

    /**
     * @return resource
     */
    public function getStream(): mixed
    {
        return $this->stream;
    }

    public function isWritable(): bool
    {
        return !$this->closed
            && is_resource($this->resource)
            && is_resource($this->stream);
    }

    public function write(string $data): void
    {
        if (!$this->isWritable()) {
            throw WriterException::unwritable();
        }

        $size = fwrite($this->stream, $data);

        if ($size === false || $size !== strlen($data)) {
            throw WriterException::failure();
        }
    }

    public function flush(): void
    {
        if (!$this->isWritable()) {
            throw WriterException::unwritable();
        }

        if (!rewind($this->stream) || stream_copy_to_stream($this->stream, $this->resource) === false) {
            throw WriterException::failure();
        }

        if (!ftruncate($this->stream, 0) || !rewind($this->stream)) {
            throw WriterException::failure();
        }

        if (!fflush($this->resource)) {
            throw WriterException::failure();
        }
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        if (is_resource($this->stream) && is_resource($this->resource)) {
            $this->flush();
        }

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        if (is_resource($this->resource)) {
            fclose($this->resource);
        }

        $this->closed = true;
    }
}
