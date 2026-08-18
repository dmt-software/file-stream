<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

class JsonDecodeDeserializer implements Deserializer
{
    /**
     * @inheritDoc
     */
    public function deserialize(string $part): object
    {
        return json_decode($part);
    }
}