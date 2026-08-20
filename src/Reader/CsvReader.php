<?php

declare(strict_types=1);

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
    public function getHeader(): object
    {
        return new ArrayObject($this->parser->getHeader(), ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * {@inheritDoc}
     *
     * @return Iterator<int, ArrayObject<string, mixed>>
     */
    public function getResults(): Iterator
    {
        foreach ($this->parser as $key => $row) {
            yield $key => new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
        }
    }
}
