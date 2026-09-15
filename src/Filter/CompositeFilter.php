<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use DMT\FileStream\Filter\Operation\CompositeMode;

/**
 * Combines multiple filters into a single filter.
 *
 * @template T of object
 * @implements FilterInterface<T>
 */
final readonly class CompositeFilter implements FilterInterface
{
    /**
     * @param list<FilterInterface<T>> $filters
     */
    public function __construct(
        private array $filters,
        private CompositeMode $mode = CompositeMode::All,
    ) {
    }

    public function accept(object $object, int $key): bool
    {
        return match ($this->mode) {
            CompositeMode::All => array_all(
                $this->filters,
                fn($filter): bool => $filter->accept($object, $key)
            ),
            CompositeMode::Any => array_any(
                $this->filters,
                fn($filter): bool => $filter->accept($object, $key)
            ),
            CompositeMode::None => !array_any(
                $this->filters,
                fn($filter): bool => $filter->accept($object, $key)
            )
        };
    }
}
