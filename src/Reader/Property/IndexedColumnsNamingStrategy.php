<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

final class IndexedColumnsNamingStrategy implements NamingStrategyInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private NamedPropertiesStrategy $namedProperties;

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        if (!isset($this->namedProperties)) {
            $this->namedProperties = new NamedPropertiesStrategy(
                array_map(fn(int $key) => sprintf('column%d', $key), array_keys($columns))
            );
        }

        return $this->namedProperties->apply($columns);
    }
}