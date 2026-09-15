<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

use DMT\FileStream\Record\Mapping\ColumnMapperInterface;
use DMT\FileStream\Record\Mapping\Csv\FlattenArrayColumnMapper;

/**
 * Defines the configuration used when writing CSV data.
 *
 * The configuration provides the CSV control settings required to produce
 * records and may include additional writer-specific behavior such as column
 * mapping.
 */
final readonly class CsvWriterConfig implements CsvControlInterface
{
    public function __construct(
        public string $delimiter = ',',
        public string $enclosure = '"',
        public string $escape = '',
        public string $lineEnding = "\n",
        public ColumnMapperInterface $columnMapper = new FlattenArrayColumnMapper(),
    ) {
    }
}