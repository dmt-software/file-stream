<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvLineParser;

final class FirstRowHeader implements HeaderInterface
{
    private array $header = [];

    public function __construct(private readonly CsvLineParser $parser)
    {
    }

    public function getHeader(): array
    {
        if ($this->header) {
            return $this->header;
        }

        if ($this->parser->key() >= 0) {
            throw new ReaderException('Already reading csv results.');
        }

        try {
            return $this->header = $this->parser->current();
        } finally {
            if ($this->parser->key() < 0) {
                $this->parser->next();
            }
        }
    }
}
