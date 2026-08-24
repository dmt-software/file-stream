<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use DMT\FileStream\Reader\Csv\CsvParser;
use InvalidArgumentException;

/**
 * @implements ParserFactoryInterface<CsvParser>
 */
final readonly class CsvParserFactory implements ParserFactoryInterface
{
    /**
     * @param array{
     *     "delimiter"?: string,
     *     "enclosure"?: string,
     *     "escape"?: string,
     * }
     * $config
     */
    public function __construct(
        private array $config = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fromFile(string $filename): CsvParser
    {
        $stream = @fopen($filename, 'r');

        if ($stream === false) {
            throw new InvalidArgumentException('Could not open file');
        }

        return new CsvParser($stream, ...$this->config);
    }

    /**
     * @inheritDoc
     */
    public function fromStream(mixed $stream): CsvParser
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid stream');
        }

        return new CsvParser($stream, ...$this->config);
    }

    /**
     * @inheritDoc
     */
    public function fromString(string $string): CsvParser
    {
        $stream = @fopen('data://text/plain,' . $string, 'r');

        if ($stream === false) {
            throw new InvalidArgumentException('Could not read string');
        }

        return new CsvParser($stream, ...$this->config);
    }
}
