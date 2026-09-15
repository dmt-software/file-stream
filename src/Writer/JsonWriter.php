<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Config\JsonWriterConfig;
use DMT\FileStream\ObjectWriter;
use DMT\FileStream\Serialization\JsonObjectSerializer;
use DMT\FileStream\Stream\JsonWriterStream;
use DMT\FileStream\Structured\StructuredWriter;

class JsonWriter implements SerializedWriterInterface
{
    private ObjectWriter $writer;

    public function __construct(
        JsonWriterStream $output,
        JsonWriterConfig $config
    ) {
        $this->writer = new ObjectWriter(
            new StructuredWriter($output, $config->template),
            new JsonObjectSerializer($config),
        );
    }

    /**
     * @inheritDoc
     */
    public function write(iterable $values): void
    {
        $this->writer->write($values);
    }
}
