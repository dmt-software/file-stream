<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Serialization;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Reader\DeserializerInterface;
use SimpleXMLElement;
use Throwable;

/**
 * Deserializes an XML string into a SimpleXMLElement.
 *
 * @implements DeserializerInterface<SimpleXMLElement>
 */
final readonly class SimpleXmlDeserializer implements DeserializerInterface
{
    public function __construct(
        private int $options = 0,
        private ?string $namespace = null,
    ) {
    }

    public function deserialize(string $data): object
    {
        if (!str_starts_with($data, '<')) {
            throw new SerializationException('Invalid XML data');
        }

        try {
            return new SimpleXMLElement(
                $data,
                $this->options,
                namespaceOrPrefix: $this->namespace ?? ''
            );
        } catch (Throwable $throwable) {
            throw new SerializationException(
                'Error deserializing XML data',
                previous: $throwable
            );
        }
    }
}
