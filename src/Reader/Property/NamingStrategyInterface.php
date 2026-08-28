<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

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
