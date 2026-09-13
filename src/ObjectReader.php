<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Reader\ObjectReaderInterface;
use Iterator;

/**
 * @template T of object
 */
class ObjectReader implements ObjectReaderInterface
{
    /**
     * @param iterable<int|string, T> $objects
     */
    public function __construct(private iterable $objects)
    {
    }

    /**
     * @inheritDoc
     */
    public function getResults(): Iterator
    {
        $key = 0;

        foreach ($this->objects as $object) {
            yield $key++ => $object;
        }
    }
}
