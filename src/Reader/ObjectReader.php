<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\ReaderInterface;
use DMT\FileStream\Serialization\DeserializerInterface;
use Iterator;

/**
 * Generic object reader that deserializes values from an iterator.
 *
 * @template T of object
 * @implements ReaderInterface<T>
 */
final readonly class ObjectReader implements ReaderInterface
{
    /**
     * @param Iterator<int, string> $iterator
     * @param DeserializerInterface<T> $deserializer
     */
    public function __construct(
        private Iterator $iterator,
        private DeserializerInterface $deserializer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getResults(): Iterator
    {
        foreach ($this->iterator as $key => $line) {
            yield $key => $this->deserializer->deserialize($line);
        }
    }
}
