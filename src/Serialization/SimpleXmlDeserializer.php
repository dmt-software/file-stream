<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use SimpleXMLElement;
use Throwable;

/**
 * @implements DeserializerInterface<string, SimpleXMLElement>
 */
final readonly class SimpleXmlDeserializer implements DeserializerInterface
{
    public function __construct(
        private ?string $namespace = null,
        private int $options = 0,
        private string $encoding = 'UTF-8',
    ) {
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string|array $part): object
    {
        if (!is_string($part) || !str_starts_with($part, '<')) {
            throw new SerializationException('Invalid data to deserialize');
        }

        if ($this->encoding !== 'UTF-8') {
            $part = iconv($this->encoding, 'UTF-8//TRANSLIT', $part);
        }

        try {
            return new SimpleXMLElement($part, $this->options, false, $this->namespace ?? '');
        } catch (Throwable $throwable) {
            throw new SerializationException('Invalid xml', previous: $throwable);
        }
    }
}
