<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use ArrayObject;
use DMT\FileStream\Csv\CsvControl;
use DMT\FileStream\Reader\Csv\CsvLineParser;
use DMT\FileStream\Reader\Property\FirstLineNamingStrategy;
use DMT\FileStream\Reader\Property\NamingStrategyInterface;
use DMT\FileStream\Reader\Property\PrefixIndexNamingStrategy;
use DMT\FileStream\Reader\Stream\CsvLineIterator;
use DMT\FileStream\Serialization\StringGetCsvDeserializer;
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
