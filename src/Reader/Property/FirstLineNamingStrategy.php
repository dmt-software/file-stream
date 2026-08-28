<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

/**
 * Uses the first record as the property names for subsequent records.
 *
 * The first record defines both the property names and the expected number of
 * columns. The naming strategy is also applied to the first record itself.
 */
final class FirstLineNamingStrategy implements NamingStrategyInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private ?NamedPropertyStrategy $namedProperties = null;

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        return ($this->namedProperties ??= new NamedPropertyStrategy($columns))->apply($columns);
    }
}
