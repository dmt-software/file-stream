<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Boundary;

/**
 * Determines whether buffered input ends at a logical record boundary.
 *
 * A boundary separates complete logical records in a stream. Implementations
 * may inspect the accumulated input to decide whether the latest physical
 * line completes the current record.
 */
interface RecordBoundaryInterface
{
    /**
     * Check whether the buffered input forms a complete record.
     */
    public function isBoundary(string $data): bool;
}
