<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Config\XmlConfig;
use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Reader\DeserializerInterface;
use SimpleXMLElement;
use Throwable;

/**
 * Deserializes an XML string into a SimpleXMLElement.
 *
 * @implements DeserializerInterface<SimpleXMLElement>
 */
final readonly class XmlElementDeserializer implements DeserializerInterface
{
    public function __construct(private XmlConfig $config = new XmlConfig())
    {
    }

    public function deserialize(string $data): object
    {
        if (!str_starts_with($data, '<')) {
            throw new SerializationException('Invalid XML element');
        }

        try {
            return new SimpleXMLElement(
                $data,
                $this->config->flags,
                namespaceOrPrefix: $this->config->namespace ?? '',
            );
        } catch (Throwable $throwable) {
            throw new SerializationException(
                'Unable to deserialize XML element',
                previous: $throwable,
            );
        }
    }
}
