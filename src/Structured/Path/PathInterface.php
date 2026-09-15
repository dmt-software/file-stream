<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Path;

use InvalidArgumentException;

/**
 * Represents a validated path as an ordered collection of segments.
 *
 * Implementations define the path syntax and are responsible for parsing,
 * validating and matching path segments. The concrete notation may differ,
 * such as dot-separated or slash-separated paths.
 *
 * The interface is format-agnostic and can be shared by selectors and
 * template parsers.
 */
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
     *
     * @param list<string|null> $segments
     */
    public function matchesPath(array $segments): bool;
}
