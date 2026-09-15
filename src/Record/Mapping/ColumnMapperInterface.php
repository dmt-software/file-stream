<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Mapping;

interface ColumnMapperInterface
{
    /**
     * @param array<string, mixed> $properties
     * @return list<scalar|null>
     */
    public function map(array $properties): array;
}
