<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use ArrayObject;
use DMT\FileStream\Reader\Csv\CsvParser;
use DMT\FileStream\Reader\Csv\Header\HeaderInterface;
use DMT\FileStream\Reader\Csv\Header\NumberedColumnsHeader;
use Iterator;

final readonly class CsvReader implements ReaderInterface
{
    private HeaderInterface $header;

    public function __construct(
        private CsvParser $parser,
        ?HeaderInterface  $header = null,
    ) {
        $this->header = $header ?? new NumberedColumnsHeader($this->parser);
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayObject<string, mixed>
     */
    public function getHeader(): object
    {
        $columns = array_map(
            fn (int $count) => $count > 1 ? 'array' : 'string',
            array_count_values($this->header->getHeader())
        );

        return new ArrayObject($columns, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * {@inheritDoc}
     *
     * @return Iterator<int, ArrayObject<string, mixed>>
     */
    public function getResults(): Iterator
    {
        $columns = $this->header->getHeader();

        foreach ($this->parser as $key => $row) {
            if ($row == $columns) {
                continue;
            }

            $line = array_slice($row, 0, count($columns));
            $line = array_merge_recursive(...array_map(fn ($k, $val) => [$k => $val], $columns, $line));

            yield $key => new ArrayObject($line, ArrayObject::ARRAY_AS_PROPS);
        }
    }
}
