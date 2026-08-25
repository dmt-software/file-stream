<?php

declare(strict_types=1);

namespace DMT\FileStream\Validator;

use DMT\FileStream\Exception\ValidationException;

interface ValidatorInterface
{
    /**
     * Validate the given header.
     *
     * @throws ValidationException
     */
    public function validate(object $header): void;
}
