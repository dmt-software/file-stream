<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Mapping;

/**
 * Maps sequential record values to named properties.
 *
 * Implementations determine how positional values are associated with
 * property names and return the resulting associative representation.
 */
interface PropertyMapperInterface
{
    /**
     * Map the given record values to named properties.
     *
     * @param list<mixed> $values
     * @return array<string, mixed>
     */
    public function map(array $values): array;
}
