<?php

declare(strict_types=1);

namespace DMT\FileStream\Validator;

use Closure;
use DMT\FileStream\Exception\ValidationException;
use TypeError;

/**
 * @implements ValidatorInterface<object>
 */
final readonly class CallbackValidator implements ValidatorInterface
{
    /**
     * The callback should return false or throw a ValidationException.
     * Any other return value will be considered valid.
     */
    public function __construct(private Closure $callback)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws TypeError When callback does not except the object type of the given header.
     */
    public function validate(object $header): void
    {
        if ($this->callback->__invoke($header) === false) {
            throw new ValidationException('Unexpected header information');
        }
    }
}