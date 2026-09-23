<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Mapping\Csv;

use DMT\FileStream\Record\Mapping\PropertyMapperInterface;


final class PrefixIndexPropertyMapper implements PropertyMapperInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private ?PredefinedPropertyMapper $mapper = null;

    public function __construct(
        private readonly string $prefix = 'column',
    ) {
    }

    /**
     * @inheritDoc
     */
    public function map(array $values): array
    {
        $this->mapper ??= new PredefinedPropertyMapper(
            array_map(
                fn(int $key) => $this->prefix . ($key + 1),
                array_keys($values)
            )
        );

        return $this->mapper->map($values);
    }
}
