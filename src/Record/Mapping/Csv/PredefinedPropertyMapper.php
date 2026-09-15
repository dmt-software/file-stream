<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\PropertyMapperInterface;

/**
 * Maps record values to a predefined set of property names.
 *
 * Values are assigned to property names by position. Missing values are
 * represented as null, while values without a corresponding property name are
 * ignored. When a property name occurs more than once, its values are grouped
 * into an array in their original order.
 */
final class PredefinedPropertyMapper implements PropertyMapperInterface
{
    /**
     * The number of property names.
     */
    private readonly int $propertyCount;

    /**
     * Indicates if the property names have duplicates.
     */
    private readonly bool $hasDuplicates;

    /**
     * @param array<int, string> $propertyNames
     */
    public function __construct(private array $propertyNames)
    {
        ksort($this->propertyNames);

        $this->hasDuplicates = max(array_count_values($this->propertyNames)) > 1;
        $this->propertyCount = max(array_keys($this->propertyNames)) + 1;
    }

    public function map(array $values): array
    {
        $values = array_slice($values, 0, $this->propertyCount);
        $values = array_pad($values, $this->propertyCount, null);
        $values = array_filter(
            $values,
            fn(int $key) => array_key_exists($key, $this->propertyNames),
            ARRAY_FILTER_USE_KEY
        );

        if (!$this->hasDuplicates) {
            return array_combine($this->propertyNames, $values);
        }

        return array_merge_recursive(
            ...array_map(fn ($k, $val) => [$k => $val], $this->propertyNames, $values)
        );
    }
}
