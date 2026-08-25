<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use Closure;
use TypeError;

final readonly class CallbackFilter implements FilterInterface
{
    public function __construct(private Closure $callback)
    {
    }

    /**
     * @throws TypeError When filter cannot be used on the current result format.
     */
    public function __invoke(object $result, int $key): bool
    {
        return $this->callback->__invoke($result, $key);
    }
}
