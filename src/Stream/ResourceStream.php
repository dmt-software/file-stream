<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Exception\WriterException;
use InvalidArgumentException;

/**
 * Provides a stream backed directly by a PHP stream resource.
 *
 * The available capabilities depend on how the underlying resource was
 * opened. Readable resources support cursor-based reads and rewinding;
 * writable resources support sequential writes and flushing.
 *
 * @implements ReadableStreamInterface<resource>
 * @implements WritableStreamInterface<resource>
 */
final class ResourceStream implements
    ReadableStreamInterface,
    WritableStreamInterface
{
    /**
     * Indicates whether the stream is seekable.
     */
    private readonly bool $seekable;

    /**
     * Indicates whether the stream has been started.
     */
    private bool $started = false;

    /**
     * Indicates whether the stream has been closed.
     */
    private bool $closed = false;

    /**
     * The current read chunk of data.
     */
    private ?string $current = null;

    /**
     * Construct a new ResourceStream instance.
     *
     * @param resource $stream A stream resource.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly mixed $stream)
    {
        StreamValidator::resource($stream);

        $this->seekable = stream_get_meta_data($stream)['seekable'] ?? false;
    }

    /**
     * @inheritDoc
     */
    public function getStream(): mixed
    {
        return $this->stream;
    }

    public function isReadable(): bool
    {
        if ($this->closed || !is_resource($this->stream)) {
            return false;
        }

        try {
            StreamValidator::readable($this->stream);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    public function next(): bool
    {
        if (!$this->isReadable()) {
            throw ReaderException::unreadable();
        }

        $data = fgetc($this->stream);

        if ($data === false) {
            return false;
        }

        $this->started = true;
        $this->current = $data;

        return true;
    }

    public function current(): string
    {
        if (!$this->started && !$this->next()) {
            throw ReaderException::empty();
        }

        return $this->current ?? '';
    }

    public function isWritable(): bool
    {
        if ($this->closed || !is_resource($this->stream)) {
            return false;
        }

        try {
            StreamValidator::writable($this->stream);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
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

        if (!fflush($this->stream)) {
            throw WriterException::failure();
        }
    }

    public function isRewindable(): bool
    {
        return is_resource($this->stream) && $this->seekable;
    }

    public function rewind(): void
    {
        if (!$this->started) {
            return;
        }

        if (!$this->isRewindable() || !rewind($this->stream)) {
            throw ReaderException::cannotRewind();
        }

        $this->started = false;
        $this->current = null;
    }

    public function eof(): bool
    {
        return $this->closed || !is_resource($this->stream) || feof($this->stream);
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

        $this->closed = fclose($this->stream);
    }
}
