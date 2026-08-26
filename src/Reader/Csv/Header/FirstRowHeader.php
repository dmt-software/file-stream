<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;
use TypeError;

final class FirstRowHeader implements HeaderInterface
{
    private array $header = [];

    public function __construct(private readonly CsvParser $parser)
    {
    }

    public function getHeader(): array
    {
        if ($this->header) {
            return $this->header;
        }

        if ($this->parser->key() >= 0) {
            throw new ReaderException('Already advanced beyond first row.');
        }

        try {
            return $this->header = $this->parser->current();
        } catch (TypeError) {
            throw new NotFoundException('Empty CSV file.');
        } finally {
            $this->parser->next();
        }
    }
}
