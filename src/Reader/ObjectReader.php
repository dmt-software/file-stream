<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\ReaderInterface;
use DMT\FileStream\Serialization\DeserializerInterface;
use Iterator;

/**
 * Generic object reader that deserializes values from an iterator.
 *
 * This class is intentionally not final, so format-specific readers can extend
 * it as convenience presets for end users. Such readers may compose their own
 * parser, selector, iterator and deserializer before delegating to this class.
 *
 * Advanced users can instantiate ObjectReader directly to provide custom
 * iterator and deserializer implementations.
 *
 * @template T of object
 * @implements ReaderInterface<T>
 */
readonly class ObjectReader implements ReaderInterface
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
