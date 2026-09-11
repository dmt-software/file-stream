<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Selector;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Structured\Selector\Path\PathInterface;
use InvalidArgumentException;
use pcrov\JsonReader\JsonReader;

/**
 * Selects JSON objects matching a configured path.
 *
 * The selector advances a JSON reader-backed stream until an object matching
 * the configured path is reached. The selected object remains the current
 * item of the stream.
 *
 * Array names are inherited by nameless objects contained directly within
 * that array. Nested arrays without an intermediate object are not supported.
 *
 * @implements SelectorInterface<ReadableStreamInterface<JsonReader>>
 */
final class JsonObjectSelector implements SelectorInterface
{
    /**
     * @var list<string>
     */
    private array $segments = [];

    /**
     * @var list<string|null>
     */
    private array $arrayNames = [];

    /**
     * The current depth of the parsed JSON.
     */
    private int $depth = -1;

    public function __construct(private readonly PathInterface $path)
    {
    }

    /**
     * Advance the JSON reader stream to the next matching object.
     *
     * The supplied stream must expose a JsonReader instance through getStream().
     *
     * @param ReadableStreamInterface<JsonReader> $stream
     *
     * @return bool True when a matching object was selected.
     * @throws ReaderException When the stream could not be read.
     * @throws InvalidArgumentException When the supplied stream is not compatible.
     */
    public function selectNext(ReadableStreamInterface $stream): bool
    {
        $reader = $stream->getStream();

        if (!$reader instanceof JsonReader) {
            throw new InvalidArgumentException(
                'JsonObjectSelector requires a JsonReaderStream'
            );
        }

        while ($stream->next()) {
            $nodeType = $reader->type();

            match ($nodeType) {
                JsonReader::OBJECT => $this->enterObject($reader),
                JsonReader::END_OBJECT => $this->leaveObject(),
                JsonReader::ARRAY => $this->enterArray($reader),
                JsonReader::END_ARRAY => $this->leaveArray(),
                default => null,
            };

            if (
                $nodeType === JsonReader::OBJECT
                && $this->path->matchesPath($this->segments)
            ) {
                return true;
            }
        }

        return false;
    }

    private function enterObject(JsonReader $reader): void
    {
        $this->depth++;

        $name = $reader->name();

        if ($name === null && $this->arrayNames !== []) {
            $name = end($this->arrayNames);
        }

        $this->segments[$this->depth] = $name;
    }

    private function leaveObject(): void
    {
        unset($this->segments[$this->depth]);

        $this->depth--;
    }

    private function enterArray(JsonReader $reader): void
    {
        $this->arrayNames[] = $reader->name();
    }

    private function leaveArray(): void
    {
        array_pop($this->arrayNames);
    }
}
