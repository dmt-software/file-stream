<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv;

use Traversable;

final readonly class CsvControl
{
    public function __construct(
        public string $delimiter = ',',
        public string $enclosure = '"',
        public string $escape = '',
        public string $lineEnding = "\n",
    ) {
    }
}
