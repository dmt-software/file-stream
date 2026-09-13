<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Config\JsonReaderConfig;
use DMT\FileStream\SerializedObjectReader;
use DMT\FileStream\Serialization\JsonObjectDeserializer;
use DMT\FileStream\Stream\JsonReaderStream;
use DMT\FileStream\Structured\Selector\JsonObjectSelector;
use DMT\FileStream\Structured\StructuredIterable;
use Iterator;

class JsonReader implements ObjectReaderInterface
{
    private SerializedObjectReader $reader;

    public function __construct(
        JsonReaderStream $stream,
        JsonReaderConfig $config
    ) {
        $this->reader = new SerializedObjectReader(
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
