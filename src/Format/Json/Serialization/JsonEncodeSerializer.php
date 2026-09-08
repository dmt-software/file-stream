<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Json\Serialization;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\SerializerInterface;
use JsonException;
use stdClass;

/**
 * Serializes an object into JSON.
 *
 * @implements SerializerInterface<stdClass>
 */
final readonly class JsonEncodeSerializer implements SerializerInterface
{
    public function __construct(private int $flags = 0)
    {
    }

    /**
     * @inheritDoc
     */
    public function serialize(object $object): string
    {
        try {
            return json_encode($object, $this->flags | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new SerializationException('Error encoding JSON data', previous: $exception);
        }
    }
}
