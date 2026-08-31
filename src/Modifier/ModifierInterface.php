<?php

declare(strict_types=1);

namespace DMT\FileStream\Modifier;

/**
 * Modifies an object while preserving its type.
 *
 * A modifier may mutate the given object or return a replacement instance,
 * but the returned object must remain compatible with the original type.
 *
 * @template T of object
 */
interface ModifierInterface
{
    /**
     * @param T $object
     * @return T
     */
    public function modify(object $object, int $key): object;
}
