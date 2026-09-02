<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

/**
 * Maps record values to a predefined list of property names.
 *
 * Missing values are filled with null and additional values are ignored.
 * Duplicate property names are combined into an array.
 */
final readonly class NamedPropertyStrategy implements NamingStrategyInterface
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

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        $columns = array_slice($columns, 0, $this->propertyCount);
        $columns = array_pad($columns, $this->propertyCount, null);

        if (!$this->hasDuplicates) {
            return array_combine($this->propertyNames, $columns);
        }

        return array_merge_recursive(
            ...array_map(fn ($k, $val) => [$k => $val], $this->propertyNames, $columns)
        );
    }
}
