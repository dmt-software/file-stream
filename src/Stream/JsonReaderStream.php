<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use InvalidArgumentException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

/**
 * Provides a readable stream backed by a JSON reader.
 *
 * The stream is constructed from a readable PHP stream resource and exposes
 * a live JSON reader instance for format-specific cursor and node operations.
 * Advancing or rewinding is managed by this wrapper, so its internal state
 * remains synchronized with the underlying reader.
 *
 * @implements ReadableStreamInterface<JsonReader>
 */
final class JsonReaderStream implements ReadableStreamInterface
{
    /**
     * The underlying stream resource.
     *
     * @var resource
     */
    private readonly mixed $resource;

    /**
     * The internal used JSON reader instance.
     */
    private readonly JsonReader $stream;

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
     * Create a new JsonReaderStream instance.
     *
     * @param resource $stream A readable stream resource.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(mixed $stream)
    {
        StreamValidator::readable($stream);

        $this->resource = $stream;
        $this->stream = new JsonReader();

        try {
            $this->stream->stream($this->resource);
        } catch (Exception) {
            $this->close();
        }

        $this->seekable = stream_get_meta_data($this->resource)['seekable'] ?? false;
    }

    public function getStream(): JsonReader
    {
        return $this->stream;
    }

    public function isReadable(): bool
    {
        return !$this->closed && is_resource($this->resource);
    }

    public function next(): bool
    {
        if (!$this->isReadable()) {
            throw ReaderException::unreadable();
        }

        $this->started = true;

        try {
            return $this->stream->read();
        } catch (Exception) {
            return false;
        }
    }

    public function current(): string
    {
        if (!$this->started && false === $this->next()) {
            throw ReaderException::empty();
        }

        try {
            return json_encode($this->stream->value()) ?: '';
        } catch (Exception) {
            return '';
        }
    }

    public function isRewindable(): bool
    {
        return is_resource($this->resource) && $this->seekable;
    }

    public function rewind(): void
    {
        if (!$this->started) {
            return;
        }

        if (!$this->isRewindable() || !rewind($this->resource)) {
            throw ReaderException::cannotRewind();
        }

        try {
            $this->stream->stream($this->resource);
        } catch (Exception) {
            $this->close();
        }

        $this->started = false;
    }

    public function eof(): bool
    {
        if ($this->closed) {
            return true;
        }

        return !is_resource($this->resource) || feof($this->resource);
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;
        $this->stream->close();

        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }
}
