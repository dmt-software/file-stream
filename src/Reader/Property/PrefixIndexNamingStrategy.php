<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

final class PrefixIndexNamingStrategy implements NamingStrategyInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private NamedPropertyStrategy $namedProperties;

    public function __construct(private readonly string $prefix = 'column')
    {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        if (!isset($this->namedProperties)) {
            $this->namedProperties = new NamedPropertyStrategy(
                array_map(fn(int $key) => sprintf('%s%d', $this->prefix, $key), array_keys($columns))
            );
        }

        return $this->namedProperties->apply($columns);
    }
}
