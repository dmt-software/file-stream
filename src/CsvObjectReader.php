<?php

declare(strict_types=1);

namespace DMT\FileStream;

use ArrayObject;
use DMT\FileStream\Format\Csv\CsvControl;
use DMT\FileStream\Format\Csv\Reader\CsvLineIterator;
use DMT\FileStream\Format\Csv\Reader\CsvLineParser;
use DMT\FileStream\Format\Csv\Reader\Property\FirstLineNamingStrategy;
use DMT\FileStream\Format\Csv\Reader\Property\NamingStrategyInterface;
use DMT\FileStream\Format\Csv\Reader\Property\PrefixIndexNamingStrategy;
use DMT\FileStream\Format\Csv\Serialization\StringGetCsvDeserializer;
use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Reader\StreamObjectReader;
use DMT\FileStream\Stream\ReadableResourceStream;
use DMT\FileStream\Stream\ReadableStreamInterface;
use InvalidArgumentException;
use Iterator;

/**
 * Reads CSV records as ArrayObject instances.
 *
 * @implements ObjectReaderInterface<ArrayObject>
 */
final class CsvObjectReader implements ObjectReaderInterface
{
    /**
     * Strategy to determine the property name for each column.
     *
     * This can be overridden.
     */
    private NamingStrategyInterface $namingStrategy;

    /**
     * CSV control settings.
     */
    private readonly CsvControl $csvControl;

    /**
     * @param resource|ReadableStreamInterface $stream
     *
     * @throws InvalidArgumentException When the stream is not a (readable) stream.
     */
    public function __construct(
        private mixed $stream {
            set => $value instanceof ReadableStreamInterface ? $value : new ReadableResourceStream($value);
        },
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '',
        string $lineEnding = "\n",
        bool $firstRowDefinesColumns = true,
    ) {
        $this->csvControl = new CsvControl($delimiter, $enclosure, $escape, $lineEnding);
        $this->namingStrategy = $firstRowDefinesColumns
            ? new FirstLineNamingStrategy()
            : new PrefixIndexNamingStrategy();
    }

    /**
     * Set the naming strategy to use.
     */
    public function setNamingStrategy(NamingStrategyInterface $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }

    /**
     * @return Iterator<int, ArrayObject>
     */
    public function getResults(): Iterator
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
        }

        $reader = new StreamObjectReader(
            new CsvLineIterator(new CsvLineParser($this->stream, $this->csvControl)),
            new StringGetCsvDeserializer($this->csvControl, $this->namingStrategy),
        );

        return $reader->getResults();
    }
}
