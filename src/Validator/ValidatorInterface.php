<?php

declare(strict_types=1);

namespace DMT\FileStream\Validator;

use DMT\FileStream\Exception\ValidationException;

/**
 * @template T of object
 */
interface ValidatorInterface
{
    /**
     * Validate the given header.
     *
     * @param T $header
     * @throws ValidationException
     */
    public function validate(object $header): void;
}
