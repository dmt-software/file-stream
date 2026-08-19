<?php

namespace DMT\FileStream\Reader;

use ArrayObject;
use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\Parser\CsvParser;
use Exception;
use Iterator;

final readonly class CsvReader implements ReaderInterface
{
    public function __construct(private CsvParser $parser)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayObject<string, mixed>
     */
    public function getHeader(?string $query = null): object
    {
        try {
            $row = $this->parser->getIterator()->current();
        } catch (Exception $exception) {
            throw new NotFoundException('header not found in csv file', previous: $exception);
        }

        return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * {@inheritDoc}
     *
     * @return Iterator<int, ArrayObject<string, mixed>>
     */
    public function getResults(?string $query = null): Iterator
    {
        foreach ($this->parser as $key => $row) {
            yield $key => new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
        }
    }
}
