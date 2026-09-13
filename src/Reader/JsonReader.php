<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Config\JsonReaderConfig;
use DMT\FileStream\ObjectReader;
use DMT\FileStream\Serialization\JsonObjectDeserializer;
use DMT\FileStream\Stream\JsonReaderStream;
use DMT\FileStream\Structured\Selector\JsonObjectSelector;
use DMT\FileStream\Structured\StructuredIterable;
use Iterator;

class JsonReader implements ObjectReaderInterface
{
    private ObjectReader $reader;

    public function __construct(
        JsonReaderStream $stream,
        JsonReaderConfig $config
    ) {
        $this->reader = new ObjectReader(
            new StructuredIterable($stream, new JsonObjectSelector($config->path)),
            new JsonObjectDeserializer($config)
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
