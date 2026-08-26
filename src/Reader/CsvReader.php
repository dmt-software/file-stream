<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\Csv\CsvParser;
use DMT\FileStream\Reader\Csv\Header\HeaderInterface;
use DMT\FileStream\Serialization\ArrayObjectDeserializer;
use DMT\FileStream\Serialization\DeserializerInterface;
use Iterator;

/**
 * @template H of object
 * @template T of object
 * @implements ReaderInterface<H, T>
 */
final readonly class CsvReader implements ReaderInterface
{
    /**
     * @param DeserializerInterface<array, T> $deserializer
     */
    public function __construct(
        private CsvParser $parser,
        private HeaderInterface $header,
        private DeserializerInterface $deserializer = new ArrayObjectDeserializer()
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader(): object
    {
        $columns = array_map(
            fn (int $count) => $count > 1 ? 'array' : 'string',
            array_count_values($this->header->getHeader())
        );

        return $this->deserializer->deserialize($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function getResults(): Iterator
    {
        $columns = $this->header->getHeader();

        $key = 0;
        foreach ($this->parser as $row) {
            if ($row == $columns) {
                continue; // skip reoccurring header lines
            }

            $line = array_slice($row, 0, count($columns));
            $line = array_merge_recursive(...array_map(fn ($k, $val) => [$k => $val], $columns, $line));

            yield $key++ => $this->deserializer->deserialize($line);
        }
    }
}
