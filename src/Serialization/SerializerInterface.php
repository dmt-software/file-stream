<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;

/**
 * Serialize an object into a string.
 *
 * @template T of object
 */
interface SerializerInterface
{
    /**
     * @param T $object
     *
     * @throws SerializationException When the object cannot be serialized.
     */
    public function serialize(object $object): string;
}
