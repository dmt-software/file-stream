<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

/**
 * Applies property names to a sequential record.
 *
 * Implementations determine how numeric record indexes are mapped to
 * associative property names.
 */
interface NamingStrategyInterface
{
    /**
     * Apply the naming strategy to the given columns.
     *
     * @param list<mixed> $columns
     * @return array<string, mixed>
     */
    public function apply(array $columns): array;
}
