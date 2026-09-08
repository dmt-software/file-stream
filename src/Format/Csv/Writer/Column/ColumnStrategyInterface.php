<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Csv\Writer\Column;

interface ColumnStrategyInterface
{
    /**
     * @param list<string, mixed> $properties
     */
    public function apply(array $properties): array;
}
