<?php

declare(strict_types=1);

namespace DMT\FileStream\Validator;

use Closure;
use DMT\FileStream\Exception\ValidationException;

final readonly class CallbackValidator implements ValidatorInterface
{
    public function __construct(private Closure $callback)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(object $header): void
    {
        if (!$this->callback->__invoke($header)) {
            throw new ValidationException('Unexpected header information');
        }
    }
}