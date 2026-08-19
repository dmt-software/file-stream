<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use InvalidArgumentException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

/**
 * @implements ParserFactoryInterface<JsonReader>
 */
class JsonParserFactory implements ParserFactoryInterface
{
    public function __construct(private readonly JsonReader $reader = new JsonReader())
    {
    }

    /**
     * @inheritDoc
     */
    public function fromFile(string $filename): JsonReader
    {
        try {
            $reader = clone $this->reader;
            $reader->open($filename);
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Invalid json file', previous: $exception);
        }

        return $reader;
    }

    /**
     * @inheritDoc
     */
    public function fromStream(mixed $stream): JsonReader
    {
        try {
            $reader = clone $this->reader;
            $reader->stream($stream);
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Invalid json file', previous: $exception);
        }

        return $reader;
    }

    /**
     * @inheritDoc
     */
    public function fromString(string $string): JsonReader
    {
        try {
            $reader = clone $this->reader;
            $reader->json($string);
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Invalid json file', previous: $exception);
        }

        return $reader;
    }
}
