<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream\Buffer;

use DMT\FileStream\Exception\WriterException;
use InvalidArgumentException;

/**
 * Buffers output data until a configured byte threshold is reached.
 *
 * Buffered data is flushed to the supplied destination stream when the
 * threshold is exceeded or when flush() is called explicitly.
 *
 * @internal
 */
final class OutputBuffer
{
    /**
     * @var resource
     */
    private mixed $stream;

    private int $size = 0;

    public function __construct(
        private readonly mixed $destination,
        private readonly int $limit = 65536,
    ) {
        if ($limit < 1) {
            throw new InvalidArgumentException(
                'Buffer limit must be greater than zero'
            );
        }

        $stream = fopen('php://memory', 'w+');

        if ($stream === false) {
            throw WriterException::failure();
        }

        $this->stream = $stream;
    }

    public function getStream(): mixed
    {
        return $this->stream;
    }

    public function write(string $data): void
    {
        $length = strlen($data);

        if ($this->size > 0 && $this->size + $length > $this->limit) {
            $this->flush();
        }

        $written = fwrite($this->stream, $data);

        if ($written === false || $written !== $length) {
            throw WriterException::failure();
        }

        $this->size += $written;

        if ($this->size >= $this->limit) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if ($this->size === 0) {
            return;
        }

        rewind($this->stream);

        $written = stream_copy_to_stream(
            $this->stream,
            $this->destination,
        );

        if ($written === false || $written !== $this->size) {
            throw WriterException::failure();
        }

        ftruncate($this->stream, 0);
        rewind($this->stream);

        $this->size = 0;
    }

    public function close(): void
    {
        $this->flush();

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
