<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use ArrayObject;
use DMT\FileStream\Exception\SerializationException;

/**
 * @implements DeserializerInterface<array,ArrayObject>
 */
final readonly class ArrayObjectDeserializer implements DeserializerInterface
{
    /**
     * @inheritDoc
     */
    public function deserialize(array|string $part): object
    {
        if (!is_array($part)) {
            throw new SerializationException('Invalid data to deserialize');
        }

        return new ArrayObject($part, ArrayObject::ARRAY_AS_PROPS);
    }
}