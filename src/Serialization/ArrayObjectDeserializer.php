<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use ArrayObject;

class ArrayObjectDeserializer implements DeserializerInterface
{
    /**
     * @inheritDoc
     */
    public function deserialize(array|string $part): object
    {
        return new ArrayObject($part, ArrayObject::ARRAY_AS_PROPS);
    }
}