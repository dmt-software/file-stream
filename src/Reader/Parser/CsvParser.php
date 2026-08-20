<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Parser;

use DMT\FileStream\Exception\NotFoundException;
use Iterator;
use IteratorAggregate;

final class CsvParser implements IteratorAggregate
{
    public function __construct(
        /** @var resource */
        private readonly mixed $stream,
        private readonly string $delimiter = ',',
        private readonly string $enclosure = '"',
        private readonly string $escape = '',
        private bool|array $header = false,
    ) {
    }

    /**
     * @return Iterator<array<string, mixed>>
     */
    public function getIterator(): Iterator
    {
        do {
            $current = fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure, $this->escape);

            if ($this->header == $current) {
                continue;
            }

            if (!is_array($this->header) && $current !== false) {
                $this->header = $this->parseHeader($current);
            }

            $line = array_slice($current, 0, count($this->header));

            yield array_merge_recursive(...array_map(fn ($key, $val) => [$key => $val], $this->header, $line));
        } while (!feof($this->stream) && $current !== false);
    }

    public function getHeader(): array
    {
        if ($this->header === false) {
            throw new NotFoundException('No header found in csv file.');
        }

        if (!is_array($this->header)) {
            $current = fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure, $this->escape);

            if ($current === false) {
                throw new NotFoundException('End of file.');
            }

            $this->header = $this->parseHeader($current);
        }

        return array_unique($this->header);
    }

    private function parseHeader(array $current): array
    {
        if ($this->header === false) {
            return array_map(fn (int $key) => 'column_' . $key, range(0, count($current) - 1));
        }

        return $current;
    }
}
