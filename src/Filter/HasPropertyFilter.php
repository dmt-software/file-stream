<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

/**
 * Filters objects by the presence of a configured property.
 *
 * A property is considered present when it exists on the object, regardless
 * of whether its value is null.
 *
 * @template T of object
 * @implements FilterInterface<T>
 */
final readonly class HasPropertyFilter implements FilterInterface
{
    public function __construct(private string $property)
    {
    }

    /**
     * @inheritDoc
     */
    public function accept(object $object, int $key): bool
    {
        return isset($object->{$this->property}) || property_exists($object, $this->property);
    }
}
