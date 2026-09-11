<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

/**
 * Defines the control settings used for reading and writing CSV data.
 *
 * The control describes how CSV records are structured, including the field
 * delimiter, enclosure character, escape character and line ending.
 *
 * A control instance can be shared by components that need to interpret or
 * produce CSV data using the same format settings.
 */
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
