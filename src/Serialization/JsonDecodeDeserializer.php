<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use InvalidArgumentException;
use stdClass;

/**
 * @implements DeserializerInterface<stdClass>
 */
final readonly class JsonDecodeDeserializer implements DeserializerInterface
{
    public function __construct(private int $flags = 0)
    {
        if ($this->flags & JSON_OBJECT_AS_ARRAY) {
            throw new InvalidArgumentException('JSON_OBJECT_AS_ARRAY is not supported.');
        }
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $data): object
    {
        if (!str_starts_with($data, '{')
            || !json_validate($data, flags: $this->flags & JSON_INVALID_UTF8_IGNORE)
        ) {
            throw new SerializationException('Invalid JSON object');
        }

        return json_decode($data, flags: $this->flags);
    }
}
