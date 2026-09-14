<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\ColumnMapperInterface;

final readonly class PredefinedNamedColumnMapper implements ColumnMapperInterface
{
    /**
     * @param array<int, string> $columnNames
     */
    public function __construct(private array $columnNames = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function map(array $properties): array
    {
        $columns = [];
        $indexes = [];

        foreach ($this->columnNames as $name) {
            $value = $properties[$name] ?? null;
            $index = $indexes[$name] ?? 0;

            $columns[] = is_array($value)
                ? ($value[$index] ?? null)
                : ($index == 0 ? $value : null);

            $indexes[$name] = $index + 1;
        }

        return $columns;
    }
}
