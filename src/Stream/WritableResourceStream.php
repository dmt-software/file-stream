<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;
use InvalidArgumentException;

class WritableResourceStream implements WritableStreamInterface, ResourceInterface
{
    private bool $writable;

    public function __construct(private mixed $resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Resource must be a resource');
        }

        if (get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException('Resource must be a stream');
        }

        $metadata = stream_get_meta_data($resource);

        $this->writable = isset($metadata['mode']) && strpbrk($metadata['mode'], 'xw+') !== false;
    }

    /**
     * @inheritDoc
     */
    public function getResource(): mixed
    {
        if (!is_resource($this->resource)) {
            throw new WriterException('Resource is not a valid resource');
        }

        return $this->resource;
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return is_resource($this->resource) && $this->writable;
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        $written = fwrite($this->resource, $data);

        if ($written === false || $written !== strlen($data)) {
            throw new WriterException('Unable to write data to stream');
        }
    }
}