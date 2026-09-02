<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Column;

final class NamedColumnStrategy implements ColumnStrategyInterface
{
    /**
     * @param list<string> $columns
     */
    public function __construct(
        private readonly array $columnNames = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $properties): array
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
