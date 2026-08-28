<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

final class FirstLineNamingStrategy implements NamingStrategyInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private NamedPropertyStrategy $namedProperties;

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        if (!isset($this->namedProperties)) {
            $this->namedProperties = new NamedPropertyStrategy($columns);
        }

        return $this->namedProperties->apply($columns);
    }
}
