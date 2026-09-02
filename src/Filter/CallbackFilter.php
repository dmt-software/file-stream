<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use Closure;
use InvalidArgumentException;
use TypeError;

/**
 * Filters objects using a callback.
 *
 * The callback receives the object and its reader key and must return whether
 * the object should be included. An incompatible callback signature is
 * reported as an InvalidArgumentException.
 *
 * @template T of object
 * @implements FilterInterface<T>
 */
final readonly class CallbackFilter implements FilterInterface
{
    /**
     * @param Closure(T, int): bool $callback
     */
    public function __construct(private Closure $callback)
    {
    }

    /**
     * @inheritDoc
     */
    public function __invoke(object $object, int $key): bool
    {
        try {
            return ($this->callback)($object, $key);
        } catch (TypeError) {
            throw new InvalidArgumentException('Callback not compatible with ObjectReader');
        }
    }
}
