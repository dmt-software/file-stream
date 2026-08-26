<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;
use TypeError;

final class NumberedColumnsHeader implements HeaderInterface
{
    private array $header = [];
    private bool $parsed = false;

    public function __construct(
        private readonly CsvParser $parser,
        private readonly bool $overridesColumnHeader = false
    ) {
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
            if (!$this->overridesColumnHeader) {
                $this->parser->next();
            }

            return $this->header = array_map(
                fn(int $key): string => 'column_' . $key, range(0, count($this->parser->current()) - 1)
            );
        } catch (TypeError) {
            throw new NotFoundException('Empty CSV file.');
        } finally {
            if ($this->overridesColumnHeader) {
                $this->parser->next();
            }
        }
    }
}
