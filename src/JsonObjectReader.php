<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Format\Json\JsonPath;
use DMT\FileStream\Format\Json\Reader\JsonObjectIterator;
use DMT\FileStream\Format\Json\Reader\JsonObjectNodeParser;
use DMT\FileStream\Format\Json\Reader\JsonObjectPathSelector;
use DMT\FileStream\Format\Json\Serialization\JsonDecodeDeserializer;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Reader\StreamObjectReader;
use DMT\FileStream\Stream\ReadableResourceStream;
use DMT\FileStream\Stream\ReadableStreamInterface;
use InvalidArgumentException;
use Iterator;
use pcrov\JsonReader\JsonReader;
use stdClass;
use Throwable;

/**
 * Reads selected JSON objects as stdClass instances.
 *
 * @implements ObjectReaderInterface<stdClass>
 */
final class JsonObjectReader implements ObjectReaderInterface
{
    private readonly JsonReader $reader;

    /**
     * @param resource|ReadableStreamInterface $stream
     * @param string|PathInterface $path
     */
    public function __construct(
        private mixed $stream {
            set => $value instanceof ReadableStreamInterface ? $value : new ReadableResourceStream($value);
        },
        private string|PathInterface $path = JsonPath::ROOT_PATH {
            set => is_string($value) ? new JsonPath($value) : $value;
        },
        private readonly int $flags = 0
    ) {
        $this->reader = new JsonReader();

        if (!$this->stream->isReadable()) {
            throw new InvalidArgumentException('Stream is not readable');
        }
    }

    /**
     * @return Iterator<int, stdClass>
     */
    public function getResults(): Iterator
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
            $this->reader->close();
        }

        if ($this->reader->type() !== JsonReader::NONE) {
            throw new ReaderException('Stream can not be rewind');
        }

        try {
            $this->reader->stream($this->stream->getResource());
        } catch (Throwable $exception) {
            throw new ReaderException('Stream is not a valid JSON stream', previous: $exception);
        }

        $parser = new JsonObjectNodeParser($this->reader);
        $reader = new StreamObjectReader(
            new JsonObjectIterator($parser, new JsonObjectPathSelector($parser, $this->path)),
            new JsonDecodeDeserializer($this->flags),
        );

        return $reader->getResults();
    }
}
