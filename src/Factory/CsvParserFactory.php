<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use DMT\FileStream\Reader\Csv\CsvLineParser;
use InvalidArgumentException;

/**
 * @implements ParserFactoryInterface<CsvLineParser>
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
        private readonly array $config = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fromFile(string $filename): CsvLineParser
    {
        $stream = @fopen($filename, 'r');

        if ($stream === false) {
            throw new InvalidArgumentException('Could not open file');
        }

        return new CsvLineParser($stream, ...$this->config);
    }

    /**
     * @inheritDoc
     */
    public function fromStream(mixed $stream): CsvLineParser
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid stream');
        }

        return new CsvLineParser($stream, ...$this->config);
    }

    /**
     * @inheritDoc
     */
    public function fromString(string $string): CsvLineParser
    {
        $stream = @fopen('data://text/plain,' . $string, 'r');

        if ($stream === false) {
            throw new InvalidArgumentException('Could not read string');
        }

        return new CsvLineParser($stream, ...$this->config);
    }
}
