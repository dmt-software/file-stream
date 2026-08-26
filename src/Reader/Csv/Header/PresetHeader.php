<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Csv\CsvParser;

final readonly class PresetHeader implements HeaderInterface
{
    public function __construct(
        private CsvParser $parser,
        private array $header,
        private bool $overridesColumnHeader = false
    ) {
    }

    public function getHeader(): array
    {
        if ($this->parser->key() >= 0) {
            throw new ReaderException('Already advanced beyond first row.');
        }

        if ($this->parser->key() == -1) {
            if ($this->overridesColumnHeader) {
                $this->parser->current();
            }
            $this->parser->next();
        }

        return $this->header;
    }
}