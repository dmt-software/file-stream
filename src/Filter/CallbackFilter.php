<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use Closure;
use InvalidArgumentException;
use TypeError;

final readonly class CallbackFilter implements FilterInterface
{
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
            throw new InvalidArgumentException('Closure not compatible with ObjectReader');
        }
    }
}
