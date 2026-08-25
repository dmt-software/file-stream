<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;

/**
 * @template ParamType of string|array
 * @template ReturnObject of object
 */
interface DeserializerInterface
{
    /**
     * @param ParamType $part
     * @return ReturnObject
     * @throws SerializationException
     */
    public function deserialize(string|array $part): object;
}
