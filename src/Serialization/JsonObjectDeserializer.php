<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Config\JsonConfig;
use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Reader\DeserializerInterface;
use stdClass;

/**
 * Deserialize JSON data into a stdClass object.
 *
 * @implements DeserializerInterface<stdClass>
 */
final readonly class JsonObjectDeserializer implements DeserializerInterface
{
    public function __construct(private JsonConfig $config = new JsonConfig())
    {
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $data): object
    {
        if (!str_starts_with($data, '{')
            || !json_validate($data, flags: $this->config->flags & JSON_INVALID_UTF8_IGNORE)
        ) {
            throw new SerializationException('Invalid JSON object');
        }

        return json_decode($data, flags: $this->config->flags);
    }
}
