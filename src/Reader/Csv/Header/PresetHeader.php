<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv\Header;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\Csv\CsvParser;

final readonly class PresetHeader implements HeaderInterface
{
    public function __construct(
        private CsvParser $parser,
        private array $header,
        private bool $skipFirstRow = false
    ) {
    }

    public function getHeader(): array
    {
        if ($this->parser->current() === false) {
            throw new NotFoundException('Empty CSV file.');
        }

        if ($this->skipFirstRow && $this->parser->key() == -1) {
            $this->parser->next();
        }

        return $this->header;
    }
}