<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use DMT\FileStream\Reader\Selector\JsonObjectPathSelector;
use DMT\FileStream\Reader\Stream\JsonObjectIterator;
use DMT\FileStream\Serialization\JsonDecodeDeserializer;
use InvalidArgumentException;
use Iterator;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;
use stdClass;

/**
 * Reads selected JSON objects as stdClass instances.
 *
 * The reader consumes the configured stream and is not rewindable.
 *
 * @implements ObjectReaderInterface<stdClass>
 */
final readonly class JsonObjectReader implements ObjectReaderInterface
{
    public const int DEFAULT_FLAGS = 0;
    public const string DEFAULT_PATH = JsonObjectPathSelector::ROOT_PATH;

    private StreamObjectReader $reader;

    /**
     * @param resource $stream
     */
    public function __construct(
        mixed $stream,
        string $path = self::DEFAULT_PATH,
        int $flags = self::DEFAULT_FLAGS
    ) {
        try {
            $jsonReader = new JsonReader();
            $jsonReader->stream($stream);
        } catch (Exception $exception) {
            throw new InvalidArgumentException(
                'Stream is not a valid JSON stream',
                previous: $exception
            );
        }

        $parser = new JsonObjectNodeParser($jsonReader);

        $this->reader = new StreamObjectReader(
            new JsonObjectIterator($parser, new JsonObjectPathSelector($parser, $path)),
            new JsonDecodeDeserializer($flags),
        );
    }

    /**
     * @return Iterator<int, stdClass>
     */
    public function getResults(): Iterator
    {
        return $this->reader->getResults();
    }
}
