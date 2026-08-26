<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use InvalidArgumentException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

/**
 * @implements ParserFactoryInterface<JsonReader>
 */
final readonly class JsonParserFactory implements ParserFactoryInterface
{
    /**
     * @param array{"options"?: int} $config
     */
    public function __construct(
        private readonly array $config = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fromFile(string $filename): JsonReader
    {
        try {
            $reader = new JsonReader(...$this->config);
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
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid stream');
        }

        try {
            $reader = new JsonReader(...$this->config);
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
            $reader = new JsonReader(...$this->config);
            $reader->json($string);
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Invalid json file', previous: $exception);
        }

        return $reader;
    }
}
