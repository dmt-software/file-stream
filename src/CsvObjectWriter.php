<?php

declare(strict_types=1);

namespace DMT\FileStream;

use ArrayObject;
use DMT\FileStream\Format\Csv\CsvControl;
use DMT\FileStream\Format\Csv\Serialization\StringPutCsvSerializer;
use DMT\FileStream\Format\Csv\Writer\Column\ColumnStrategyInterface;
use DMT\FileStream\Format\Csv\Writer\Column\FlattenArrayColumnStrategy;
use DMT\FileStream\Format\Csv\Writer\CsvStreamWriter;
use DMT\FileStream\Stream\WritableResourceStream;
use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\ObjectWriterInterface;
use DMT\FileStream\Writer\StreamObjectWriter;

/**
 * Writes ArrayObject instances as CSV records.
 *
 * @implements ObjectWriterInterface<ArrayObject>
 */
final class CsvObjectWriter implements ObjectWriterInterface
{
    /**
     * Strategy to determine the columns in the CSV file.
     *
     * This can be overridden.
     */
    private ColumnStrategyInterface $columnStrategy;

    /**
     * CSV control settings.
     */
    private readonly CsvControl $csvControl;

    /**
     * @param resource|WritableStreamInterface $stream
     */
    public function __construct(
        private mixed $stream {
            set => $value instanceof WritableStreamInterface ? $value : new WritableResourceStream($value);
        },
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '',
        string $lineEnding = "\n",
    ) {
        $this->columnStrategy = new FlattenArrayColumnStrategy();
        $this->csvControl = new CsvControl($delimiter, $enclosure, $escape, $lineEnding);
    }

    /**
     * Set the column strategy to use.
     */
    public function setColumnStrategy(ColumnStrategyInterface $columnStrategy): self
    {
        $this->columnStrategy = $columnStrategy;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param iterable<int, ArrayObject> $objects
     */
    public function write(iterable $objects): void
    {
        $writer = new StreamObjectWriter(
            new CsvStreamWriter($this->stream, $this->csvControl),
            new StringPutCsvSerializer($this->csvControl, $this->columnStrategy),
        );

        $writer->write($objects);
    }
}
