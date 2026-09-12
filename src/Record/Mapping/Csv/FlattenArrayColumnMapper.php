<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\ColumnMapperInterface;

/**
 * Maps properties to a fixed-width list of scalar column values.
 *
 * Array values are flattened to their first value, non-scalar values are
 * replaced with null, and the number of columns is determined by the first
 * mapped record.
 */
final class FlattenArrayColumnMapper implements ColumnMapperInterface
{
    public function __construct(
        private ?int $columnCount = null
    ) {
    }
    
    public function map(array $properties): array
    {
        $this->columnCount ??= count($properties);

        foreach ($properties as &$value) {
            if (is_array($value)) {
                $value = array_shift($value);
            }

            if (!is_scalar($value)) {
                $value = null;
            }
        }

        $properties = array_slice($properties, 0, $this->columnCount);

        return array_pad($properties, $this->columnCount, null);
    }
}
