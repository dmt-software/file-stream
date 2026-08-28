<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Property;

final readonly class IndexedColumnNamingStrategy implements NamingStrategyInterface
{
    private NamedPropertiesStrategy $namedProperties;

    /**
     * @param array<int, string> $mapping
     */
    public function __construct(private array $mapping)
    {
        ksort($this->mapping);

        $this->namedProperties = new NamedPropertiesStrategy(array_values($this->mapping));
    }

    /**
     * @inheritDoc
     */
    public function apply(array $columns): array
    {
        $columns = array_filter($columns, fn(int $key) => array_key_exists($key, $this->mapping), ARRAY_FILTER_USE_KEY);

        return $this->namedProperties->apply($columns);
    }
}