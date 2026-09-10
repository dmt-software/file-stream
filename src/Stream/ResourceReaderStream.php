<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use InvalidArgumentException;

/**
 * Provides a readable stream backed directly by a PHP stream resource.
 *
 * The stream reads fixed-size chunks from the underlying resource. The
 * current chunk can be inspected without advancing, and seekable resources
 * can be rewound to their initial position.
 *
 * @implements ReadableStreamInterface<resource>
 */
final class ResourceReaderStream implements ReadableStreamInterface
{
    /**
     * Indicates whether the underlying stream is seekable.
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
     * The current chunk of data.
     */
    private ?string $current = null;

    /**
     * @param resource $stream A readable stream resource.
     * @param positive-int|null $chunkSize The number of bytes to read at a time.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly mixed $stream,
        private readonly ?int $chunkSize = null,
    ) {
        StreamValidator::readable($stream);

        if (!is_null($chunkSize) && $chunkSize < 1) {
            throw new InvalidArgumentException('Chunk size must be greater than zero');
        }

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

        $data = fread($this->stream, $this->chunkSize);

        if ($data === false) {
            return false;
        }

        if ($data === '' && !$this->started) {
            return false;
        }

        $this->started = true;
        $this->current = $data;

        return true;
    }

    public function current(): string
    {
        if (!$this->started && false === $this->next()) {
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
        if ($this->closed) {
            return true;
        }

        return !is_resource($this->stream) || feof($this->stream);
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
