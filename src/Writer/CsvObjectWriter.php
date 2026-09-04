<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use ArrayObject;
use DMT\FileStream\Csv\CsvControl;
use DMT\FileStream\Serialization\StringPutCsvSerializer;
use DMT\FileStream\Writer\Column\ColumnStrategyInterface;
use DMT\FileStream\Writer\Column\FlattenArrayColumnStrategy;
use DMT\FileStream\Writer\Stream\CsvStreamWriter;

/**
 * Writes ArrayObject instances as CSV records.
 *
 * @implements ObjectWriterInterface<ArrayObject>
 */
final class CsvObjectWriter implements ObjectWriterInterface
{
    private ColumnStrategyInterface $columnStrategy;
    private CsvControl $csvControl;
    private CsvStreamWriter $streamWriter;

    /**
     * @param resource $stream
     */
    public function __construct(
        mixed $stream,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '',
        string $lineEnding = "\n",
    ) {
        $this->columnStrategy = new FlattenArrayColumnStrategy();

        $this->csvControl = new CsvControl(
            delimiter: $delimiter,
            enclosure: $enclosure,
            escape: $escape,
            lineEnding: $lineEnding,
        );

        $this->streamWriter = new CsvStreamWriter(
            stream: $stream,
            control: $this->csvControl,
        );
    }

    public function setColumnStrategy(
        ColumnStrategyInterface $columnStrategy
    ): self {
        $this->columnStrategy = $columnStrategy;

        return $this;
    }

    /**
     * @param iterable<int, ArrayObject> $objects
     */
    public function write(iterable $objects): void
    {
        $writer = new StreamObjectWriter(
            writer: $this->streamWriter,
            serializer: new StringPutCsvSerializer(
                control: $this->csvControl,
                columnStrategy: $this->columnStrategy,
            ),
        );

        $writer->write($objects);
    }
}
