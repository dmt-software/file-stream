<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use DMT\FileStream\Filter\Operation\ComparisonOperator;

/**
 * Filters objects by comparing a property value.
 *
 * Missing properties are treated as null, allowing their handling to be
 * controlled by the configured comparison operation.
 *why do we need a not filter?
 * @template T of object
 * @implements FilterInterface<T>
 */
final readonly class PropertyFilter implements FilterInterface
{
    public function __construct(
        private string $property,
        private mixed $value,
        private ComparisonOperator $operator = ComparisonOperator::Equal,
    ) {
    }

    public function accept(object $object, int $key): bool
    {
        return $this->operator->compare(
            $object->{$this->property} ?? null,
            $this->value
        );
    }
}
