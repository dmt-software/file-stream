<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;

/**
 * Deserializes string data into an object.
 *
 * @template T of object
 */
interface DeserializerInterface
{
    /**
     * Convert serialized string data into an object.
     *
     * @return T
     * @throws SerializationException When the data cannot be deserialized.
     */
    public function deserialize(string $data): object;
}
