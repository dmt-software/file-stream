<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Config\XmlWriterConfig;
use DMT\FileStream\ObjectWriter;
use DMT\FileStream\Serialization\XmlElementSerializer;
use DMT\FileStream\Stream\XmlWriterStream;
use DMT\FileStream\Structured\StructuredWriter;

class XmlWriter implements SerializedWriterInterface
{
    private ObjectWriter $writer;

    public function __construct(
        XmlWriterStream $output,
        XmlWriterConfig $config
    ) {
        $this->writer = new ObjectWriter(
            new StructuredWriter($output, $config->template),
            new XmlElementSerializer()
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
