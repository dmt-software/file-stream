<?php

declare(strict_types=1);

namespace DMT\FileStream\Transformer;

/**
 * Transforms an object into another object.
 *
 * @template T of object
 * @template R of object
 */
interface TransformerInterface
{
    /**
     * @param T $object
     * @return R
     */
    public function transform(object $object): object;
}