<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Column;

interface ColumnStrategyInterface
{
    /**
     * @param list<string, mixed> $properties
     */
    public function apply(array $properties): array;
}
