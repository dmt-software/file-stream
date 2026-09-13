<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Config\JsonConfigInterface;
use DMT\FileStream\Config\JsonWriterConfig;
use DMT\FileStream\Exception\SerializationException;
use JsonException;
use stdClass;

/**
 * Serializes an object into JSON.
 *
 * @implements SerializerInterface<stdClass>
 */
final readonly class JsonObjectSerializer implements SerializerInterface
{
    public function __construct(
        private JsonConfigInterface $config = new JsonWriterConfig()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function serialize(object $object): string
    {
        try {
            return json_encode(
                $object,
                $this->config->flags | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new SerializationException(
                'Error encoding JSON data',
                previous: $exception
            );
        }
    }
}
