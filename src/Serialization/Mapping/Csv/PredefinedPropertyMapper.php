<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization\Mapping\Csv;

use DMT\FileStream\Serialization\Mapping\PropertyNamingMapperInterface;

/**
 * Maps record values to a predefined set of property names.
 *
 * Values are assigned to property names by position. Missing values are
 * represented as null, while values without a corresponding property name are
 * ignored. When a property name occurs more than once, its values are grouped
 * into an array in their original order.
 */
final readonly class PredefinedPropertyMapper implements PropertyNamingMapperInterface
{
    /**
     * The number of property names.
     */
    private int $propertyCount;

    /**
     * Indicates if the property names have duplicates.
     */
    private bool $hasDuplicates;

    /**
     * @param list<string> $propertyNames
     */
    public function __construct(private array $propertyNames)
    {
        $this->hasDuplicates = max(array_count_values($this->propertyNames)) > 1;
        $this->propertyCount = count($this->propertyNames);
    }

    public function map(array $values): array
    {
        $values = array_slice($values, 0, $this->propertyCount);
        $values = array_pad($values, $this->propertyCount, null);

        if (!$this->hasDuplicates) {
            return array_combine($this->propertyNames, $values);
        }

        return array_merge_recursive(
            ...array_map(fn ($k, $val) => [$k => $val], $this->propertyNames, $values)
        );
    }
}
