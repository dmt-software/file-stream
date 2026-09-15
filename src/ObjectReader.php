<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Reader\SerializedIterableInterface;
use DMT\FileStream\Serialization\DeserializerInterface;
use Iterator;

/**
 * Deserializes string values into objects.
 *
 * @template T of object
 * @implements ObjectReaderInterface<T>
 */
final readonly class ObjectReader implements ObjectReaderInterface
{
    /**
     * @param SerializedIterableInterface<int, string> $values
     * @param DeserializerInterface<T> $deserializer
     */
    public function __construct(
        private SerializedIterableInterface $values,
        private DeserializerInterface $deserializer,
    ) {
    }

    /**
     * @return Iterator<int, T>
     */
    public function getResults(): Iterator
    {
        foreach ($this->values as $key => $value) {
            yield $key => $this->deserializer->deserialize($value);
        }
    }
}
