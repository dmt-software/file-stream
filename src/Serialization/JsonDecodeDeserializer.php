<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use JsonException;
use stdClass;

/**
 * @implements DeserializerInterface<string, stdClass>
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
    public function deserialize(string|array $part): object
    {
        if (!is_string($part) || !str_starts_with($part, '{')) {
            throw new SerializationException('Invalid data to deserialize');
        }

        try {
            return json_decode($part, flags: $this->options | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new SerializationException('Invalid json', previous: $exception);
        }
    }
}
