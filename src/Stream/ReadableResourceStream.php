<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;
use InvalidArgumentException;

/**
 * Wraps a PHP stream resource.
 */
final readonly class ReadableResourceStream implements ReadableStreamInterface, ResourceInterface
{
    private bool $seekable;
    private bool $readable;

    public function __construct(private mixed $resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Resource must be a resource');
        }

        if (get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException('Resource must be a stream');
        }

        $metadata = stream_get_meta_data($resource);

        $this->seekable = $metadata['seekable'] ?? false;
        $this->readable = isset($metadata['mode']) && strpbrk($metadata['mode'], 'r+') !== false;
    }

    /**
     * @inheritDoc
     */
    public function getResource(): mixed
    {
        if (!is_resource($this->resource)) {
            throw new ReaderException('Resource is not a valid resource');
        }

        return $this->resource;
    }

    /**
     * @inheritDoc
     */
    public function read(?int $length = null): string
    {
        $data = fread($this->resource, $length);

        if ($data === false) {
            throw new ReaderException('Error reading from stream');
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        if (!$this->isSeekable()) {
            throw new ReaderException('Stream is not seekable');
        }

        rewind($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return is_resource($this->resource) && $this->readable;
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return is_resource($this->resource) && $this->seekable;
    }

    /**
     * @inheritDoc
     */
    public function endOfFile(): bool
    {
        return !is_resource($this->resource) || feof($this->resource);
    }
}
