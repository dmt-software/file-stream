<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use Iterator;

/**
 * @template T of object
 *
 * @implements ObjectReaderInterface<T>
 */
final readonly class IterableObjectReader implements ObjectReaderInterface
{
    /**
     * @param iterable<int, T> $objects
     */
    public function __construct(
        private iterable $objects,
    ) {
    }

    /**
     * @return Iterator<int, T>
     */
    public function getResults(): Iterator
    {
        yield from $this->objects;
    }
}
