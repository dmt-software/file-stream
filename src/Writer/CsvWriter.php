<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Config\CsvWriterConfig;
use DMT\FileStream\ObjectWriter;
use DMT\FileStream\Record\RecordWriter;
use DMT\FileStream\Serialization\CsvRecordSerializer;
use DMT\FileStream\Stream\ResourceWriterStream;

class CsvWriter implements SerializedWriterInterface
{
    private ObjectWriter $writer;

    public function __construct(
        ResourceWriterStream $output,
        CsvWriterConfig $config = new CsvWriterConfig(),
    ) {
        $this->writer = new ObjectWriter(
            new RecordWriter($output),
            new CsvRecordSerializer($config, $config->columnMapper),
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