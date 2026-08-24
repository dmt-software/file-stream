<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv;

use DMT\FileStream\Exception\ReaderException;
use Iterator;

final class CsvLineParser implements Iterator
{
    private array|false|null $current = null;
    private int $key = -1;

    public function __construct(
        /** @var resource */
        private readonly mixed $stream,
        private readonly string $delimiter = ',',
        private readonly string $enclosure = '"',
        private readonly string $escape = '',
    ) {
    }

    public function current(): array
    {
        return $this->current ??= fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure, $this->escape);
    }

    public function next(): void
    {
        try {
            $this->current = fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure, $this->escape);
        } finally {
            $this->key++;
        }
    }

    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return !(feof($this->stream) && $this->current === false);
    }

    public function rewind(): void
    {
        if ($this->stream !== null) {
            if (!@rewind($this->stream)) {
                throw new ReaderException('Could not rewind CSV stream');
            }

            $this->key = -1;
            $this->current = null;

        }
    }
}
