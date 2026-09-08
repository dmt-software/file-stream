<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Csv;

use ArrayObject;
use DMT\FileStream\Format\Csv\Reader\CsvLineIterator;
use DMT\FileStream\Format\Csv\Reader\CsvLineParser;
use DMT\FileStream\Format\Csv\Reader\Property\FirstLineNamingStrategy;
use DMT\FileStream\Format\Csv\Reader\Property\NamingStrategyInterface;
use DMT\FileStream\Format\Csv\Reader\Property\PrefixIndexNamingStrategy;
use DMT\FileStream\Format\Csv\Serialization\StringGetCsvDeserializer;
use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Reader\StreamObjectReader;
use Iterator;

/**
 * Reads CSV records as ArrayObject instances.
 *
 * The reader consumes the configured stream and is not rewindable.
 *
 * @implements ObjectReaderInterface<ArrayObject>
 */
final class CsvObjectReader implements ObjectReaderInterface
{
    private NamingStrategyInterface $namingStrategy;
    private CsvControl $csvControl;
    private CsvLineIterator $streamIterator;

    /**
     * @param resource $stream
     */
    public function __construct(
        mixed $stream,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '',
        string $lineEnding = "\n",
        bool $firstRowDefinesColumns = true,
    ) {
        $this->namingStrategy = $firstRowDefinesColumns
            ? new FirstLineNamingStrategy()
            : new PrefixIndexNamingStrategy();

        $this->csvControl = new CsvControl(
            delimiter: $delimiter,
            enclosure: $enclosure,
            escape: $escape,
            lineEnding: $lineEnding,
        );

        $this->streamIterator = new CsvLineIterator(
            parser: new CsvLineParser(
                stream: $stream,
                control: $this->csvControl
            )
        );
    }

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
        $reader = new StreamObjectReader(
            $this->streamIterator,
            new StringGetCsvDeserializer(
                control: $this->csvControl,
                namingStrategy: $this->namingStrategy,
            ),
        );

        return $reader->getResults();
    }
}
