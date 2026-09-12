<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

/**
 * Defines the control settings used for reading and writing CSV data.
 *
 * The control describes how CSV records are structured, including the field
 * delimiter, enclosure character, escape character and line ending.
 *
 * A control instance can be used by components that need to interpret or
 * produce CSV data.
 */
interface CsvControlInterface
{
    public string $delimiter { get; }
    public string $enclosure { get; }
    public string $escape { get; }
    public string $lineEnding { get; }
}
