<?php

declare(strict_types=1);

namespace DMT\FileStream\Path;

use InvalidArgumentException;

interface PathInterface
{
    /**
     * Get the path segments.
     *
     * @return list<string>
     * @throws InvalidArgumentException
     */
    public function getSegments(): array;

    /**
     * Check if the current segments match the path of this instance.
     */
    public function matchesPath(array $segments): bool;
}
