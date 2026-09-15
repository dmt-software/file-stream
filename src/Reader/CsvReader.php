<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use ArrayObject;
use DMT\FileStream\Config\CsvReaderConfig;
use DMT\FileStream\ObjectReader;
use DMT\FileStream\Record\Boundary\CsvRecordBoundary;
use DMT\FileStream\Record\RecordIterable;
use DMT\FileStream\Serialization\CsvRecordDeserializer;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Stream\ResourceReaderStream;
use Iterator;

/**
 * Reads CSV records as ArrayObject instances.
 *
 * The reader combines CSV record boundary detection, property mapping and
 * deserialization into a convenient object-reader implementation.
 *
 * @implements ObjectReaderInterface<ArrayObject>
 */
final readonly class CsvReader implements ObjectReaderInterface
{
    private ObjectReader $reader;

    public function __construct(
        ResourceReaderStream $stream,
        CsvReaderConfig $config = new CsvReaderConfig(),
    ) {
        $this->reader = new ObjectReader(
            new RecordIterable($stream, new CsvRecordBoundary($config)),
            new CsvRecordDeserializer($config, $config->propertyMapper)
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
