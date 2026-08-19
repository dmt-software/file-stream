<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Parser;

use Iterator;
use IteratorAggregate;

class CsvParser implements IteratorAggregate
{
    public function __construct(
        /** @var resource */
        private mixed $stream,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '',
        private bool $header = false,
    ) {
    }

    /**
     * @return Iterator<array<string, mixed>>
     */
    public function getIterator(): Iterator
    {
        static $header = null;

        do {
            $current = fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure, $this->escape);

            if ($header == $current) {
                continue;
            }

            if (!$header && $current !== false) {
                $header = $this->parseHeader($current);
            }

            $line = array_slice($current, 0, count($header));

            yield array_merge_recursive(...array_map(fn ($key, $val) => [$key => $val], $header, $line));
        } while (!feof($this->stream) && $current !== false);
    }

    private function parseHeader(array $current): array
    {
        if ($this->header === false) {
            return array_map(fn (int $key) => 'column_' . $key, range(0, count($current) - 1));
        }

        return $current;
    }
}
