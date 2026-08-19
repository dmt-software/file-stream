<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use JsonException;
use stdClass;

/**
 * @implements DeserializerInterface<stdClass>
 */
final readonly class JsonDecodeDeserializer implements DeserializerInterface
{
    public function __construct(
        private int $options = JSON_THROW_ON_ERROR)
    {
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $part): object
    {
        try {
            return json_decode($part, flags: $this->options);
        } catch (JsonException $exception) {
            throw new SerializationException('Invalid json', previous: $exception);
        }
    }
}