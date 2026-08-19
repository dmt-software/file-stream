<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;

/**
 * @template T of object
 */
interface DeserializerInterface
{
    /**
     * @return T
     * @throws SerializationException
     */
    public function deserialize(string $part): object;
}
