<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use InvalidArgumentException;
use XMLWriter;

/**
 * Provides a writable stream backed by XMLWriter.
 *
 * The stream is constructed from a writable PHP stream resource and exposes
 * a live XMLWriter instance for XML-specific write operations. XML is written
 * through an internal XMLWriter buffer and flushed to the underlying resource
 * by this wrapper.
 *
 * @implements WritableStreamInterface<XMLWriter>
 */
final class XmlWriterStream implements WritableStreamInterface
{
    /**
     * The underlying stream resource.
     *
     * @var resource
     */
    private readonly mixed $resource;

    /**
     * The internal used XMLWriter instance.
     */
    private readonly XMLWriter $stream;

    /**
     * Indicates whether the stream has been closed.
     */
    private bool $closed = false;

    /**
     * Create a new XmlWriterStream instance.
     *
     * @param resource $stream A writable stream resource.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(mixed $stream)
    {
        StreamValidator::writable($stream);

        $this->resource = $stream;
        $this->stream = XMLWriter::toMemory();
    }

    public function getStream(): mixed
    {
        return $this->stream;
    }

    public function isWritable(): bool
    {
        return !$this->closed && is_resource($this->resource);
    }

    public function write(string $data): void
    {
        if (!$this->isWritable()) {
            throw WriterException::unwritable();
        }

        if (!$this->stream->writeRaw($data)) {
            throw WriterException::unwritable();
        }
    }

    public function flush(): void
    {
        if (!is_resource($this->resource)) {
            throw WriterException::unwritable();
        }

        $data = $this->stream->flush();
        $size = fwrite($this->resource, $data);

        if ($size === false || $size !== strlen($data)) {
            throw WriterException::unwritable();
        }
    }

    public function close(): void
    {
        if (!is_resource($this->resource)) {
            $this->closed = true;
        }

        if ($this->closed) {
            return;
        }

        $this->flush();
        $this->closed = fclose($this->resource);
    }
}