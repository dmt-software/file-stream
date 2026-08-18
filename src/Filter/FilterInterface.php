<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

interface FilterInterface
{
    /**
     * Call the filter on the given result.
     */
    public function __invoke(object $result, int $key): bool;
}
