<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use DMT\FileStream\Stream\Buffer\OutputBuffer;
use InvalidArgumentException;
use XMLWriter;

/**
 * Provides a buffered writable stream backed by XMLWriter.
 *
 * XML is produced through an in-memory XMLWriter and periodically transferred
 * to an output buffer. The output buffer subsequently flushes its contents to
 * the underlying output resource.
 *
 * @implements WritableStreamInterface<XMLWriter>
 */
final class XmlWriterStream implements WritableStreamInterface
{
    private readonly XMLWriter $stream;

    private readonly OutputBuffer $buffer;

    private bool $closed = false;

    /**
     * @param resource $resource
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly mixed $resource,
        int $bufferSize = 65536,
    ) {
        StreamValidator::writable($resource);

        $this->stream = XMLWriter::toMemory();
        $this->buffer = new OutputBuffer(
            destination: $resource,
            limit: $bufferSize,
        );
    }

    public function getStream(): XMLWriter
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
            throw WriterException::failure();
        }

        $this->flushBuffer();
    }

    public function flush(): void
    {
        if (!$this->isWritable()) {
            throw WriterException::unwritable();
        }

        $this->flushBuffer();
        $this->buffer->flush();
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        if (!is_resource($this->resource)) {
            $this->closed = true;

            return;
        }

        $this->flushBuffer();
        $this->buffer->close();
        $this->closed = fclose($this->resource);
    }

    private function flushBuffer(): void
    {
        $data = $this->stream->flush();

        if ($data === '') {
            return;
        }

        $this->buffer->write($data);
    }
}
