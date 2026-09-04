<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use InvalidArgumentException;
use SimpleXMLElement;

/**
 * @implements SerializerInterface<SimpleXMLElement>
 */
class SimpleXmlSerializer implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize(object $object): string
    {
        if (!$object instanceof SimpleXMLElement) {
            throw new InvalidArgumentException('Expected SimpleXMLElement');
        }

        $xml = $object->asXML();

        if ($xml === false) {
            throw new SerializationException('Error encoding XML data');
        }

        $xml = preg_replace('~^<\?xml\b[^?]*\?>\s*~', '', $xml);

        if ($xml === null) {
            throw new SerializationException('Error encoding XML data');
        }

        return $xml;
    }
}
