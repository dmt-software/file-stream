<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

use DMT\FileStream\Record\Mapping\Csv\SpreadsheetColumnPropertyMapper;
use DMT\FileStream\Record\Mapping\PropertyMapperInterface;

/**
 * Defines the configuration used when reading CSV data.
 *
 * The configuration provides the CSV control settings required to interpret
 * records and may include additional reader-specific behavior such as property
 * mapping.
 *
 * @implements CsvControlInterface
 */
final readonly class CsvReaderConfig implements CsvControlInterface
{
    public function __construct(
        public string $delimiter = ',',
        public string $enclosure = '"',
        public string $escape = '',
        public string $lineEnding = "\n",
        public PropertyMapperInterface $propertyMapper = new SpreadsheetColumnPropertyMapper(),
    ) {
    }
}