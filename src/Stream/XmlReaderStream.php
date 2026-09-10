<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use InvalidArgumentException;
use XMLReader;

/**
 * Provides a readable stream backed by XMLReader.
 *
 * The stream is constructed from a PHP stream resource and exposes a live
 * XMLReader instance for XML-specific cursor operations. Advancing or
 * rewinding is managed by this wrapper, so its internal state remains
 * synchronized with the underlying reader.
 *
 * @implements ReadableStreamInterface<XMLReader>
 */
final class XmlReaderStream implements ReadableStreamInterface
{
    /**
     * The internal used XMLReader instance.
     */
    private readonly XMLReader $stream;

    /**
     * The stream uri or null if not available.
     */
    private readonly null|string $uri;

    /**
     * Indicates whether the stream has been started.
     */
    private bool $started = false;

    /**
     * Indicates whether the stream has been closed.
     */
    private bool $closed = false;

    /**
     * Create a new XmlReaderStream instance.
     *
     * @param resource $stream A readable stream resource.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(mixed $stream)
    {
        StreamValidator::readable($stream);

        $this->stream = XMLReader::fromStream($stream, 'UTF-8');
        $this->uri = stream_get_meta_data($stream)['uri'] ?? null;
    }

    public function getStream(): XMLReader
    {
        return $this->stream;
    }

    public function isReadable(): bool
    {
        return !$this->closed;
    }

    public function next(): bool
    {
        if (!$this->isReadable()) {
            throw ReaderException::unreadable();
        }

        $this->started = true;

        return $this->stream->read();
    }

    public function current(): string
    {
        if (!$this->started && false === $this->next()) {
            throw ReaderException::empty();
        }

        return $this->stream->readOuterXml();
    }

    public function isRewindable(): bool
    {
        return isset($this->uri) && str_starts_with($this->uri, 'php://');
    }

    public function rewind(): void
    {
        if (!$this->started) {
            return;
        }

        if (!$this->isRewindable()) {
            throw ReaderException::cannotRewind();
        }

        $this->started = false;
        $this->closed = !$this->stream->open($this->uri, 'UTF-8');
    }

    public function eof(): bool
    {
        if ($this->closed) {
            return true;
        }

        return $this->started && $this->stream->nodeType === XMLReader::NONE;
    }

    public function close(): void
    {
        $this->closed = $this->stream->close();
    }
}
