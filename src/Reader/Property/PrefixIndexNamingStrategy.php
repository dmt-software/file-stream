<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

/**
 * Generates property names from each column index using a configurable prefix.
 *
 * The number of generated properties is determined by the first record and is
 * reused for later records.
 */
final class PrefixIndexNamingStrategy implements NamingStrategyInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private ?NamedPropertyStrategy $namedProperties = null;

    public function __construct(private readonly string $prefix = 'column')
    {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        return ($this->namedProperties ??= new NamedPropertyStrategy(
            array_map(fn(int $key) => sprintf('%s%d', $this->prefix, $key), array_keys($columns))
        ))->apply($columns);
    }
}
