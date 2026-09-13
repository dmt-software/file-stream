<?php

namespace DMT\FileStream\Reader;

use DMT\FileStream\Config\XmlReaderConfig;
use DMT\FileStream\ObjectReader;
use DMT\FileStream\Serialization\XmlElementDeserializer;
use DMT\FileStream\Stream\XmlReaderStream;
use DMT\FileStream\Structured\Selector\XmlElementSelector;
use DMT\FileStream\Structured\StructuredIterable;
use Iterator;

final readonly class XmlReader implements ObjectReaderInterface
{
    private ObjectReader $reader;

    public function __construct(
        XmlReaderStream $stream,
        XmlReaderConfig $config
    ) {
        $this->reader = new ObjectReader(
            new StructuredIterable($stream, new XmlElementSelector($config->path)),
            new XmlElementDeserializer($config)
        );
    }

    /**
     * @inheritDoc
     */
    public function getResults(): Iterator
    {
        yield from $this->reader->getResults();
    }
}
