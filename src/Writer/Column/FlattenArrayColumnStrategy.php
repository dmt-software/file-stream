<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Column;

final class FlattenArrayColumnStrategy implements ColumnStrategyInterface
{
    public function __construct(
        private ?int $columnCount = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $properties): array
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