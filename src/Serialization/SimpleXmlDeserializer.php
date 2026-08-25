<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use Exception;
use SimpleXMLElement;

/**
 * @implements DeserializerInterface<SimpleXMLElement>
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
        if ($this->encoding !== 'UTF-8') {
            $part = iconv($this->encoding, 'UTF-8//TRANSLIT', $part);
        }

        try {
            return new SimpleXMLElement($part, $this->options, false, $this->namespace ?? '');
        } catch (Exception $throwable) {
            throw new SerializationException('Invalid xml', previous: $throwable);
        }
    }
}
