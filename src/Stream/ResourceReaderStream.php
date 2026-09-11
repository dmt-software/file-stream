<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use InvalidArgumentException;

/**
 * Provides a readable stream backed directly by a PHP stream resource.
 *
 * The stream exposes the underlying resource while providing cursor-based
 * character reading, current-value access and optional rewinding when the
 * resource is seekable.
 *
 * @implements ReadableStreamInterface<resource>
 */
final class ResourceReaderStream implements ReadableStreamInterface
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
     * Construct a readable resource stream.
     *
     * @param resource $stream
     *
     * @throws InvalidArgumentException When the supplied resource is not readable.
     */
    public function __construct(private readonly mixed $stream)
    {
        StreamValidator::readable($stream);

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
        return !$this->closed && is_resource($this->stream);
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
