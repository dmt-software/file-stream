<?php

declare(strict_types=1);

namespace DMT\FileStream\Modifier;

use Closure;
use InvalidArgumentException;
use TypeError;

/**
 * Modifies objects using a callback.
 *
 * The callback receives the object and its reader key. It may mutate the
 * original object or return a replacement instance of the same type.
 *
 * @template T of object
 *
 * @implements ModifierInterface<T>
 */
final readonly class CallbackModifier implements ModifierInterface
{
    /**
     * @param Closure(T, int): T $callback
     */
    public function __construct(
        private Closure $callback,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function modify(object $object, int $key): object
    {
        try {
            return ($this->callback)($object, $key);
        } catch (TypeError) {
            throw new InvalidArgumentException('Callback not compatible with ObjectReader');
        }
    }
}
