<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use InvalidArgumentException;

/**
 * @template T of object
 */
interface ParserFactoryInterface
{
    /**
     * Create a new parser to read from a file.
     *
     * @return T
     * @throws InvalidArgumentException
     */
    public function fromFile(string $filename): object;

    /**
     * Create a new parser to read from a resource stream.
     *
     * @param resource $stream
     *
     * @return T
     * @throws InvalidArgumentException
     */
    public function fromStream(mixed $stream): object;

    /**
     * Create a new parser to read from a string.
     *
     * @return T
     * @throws InvalidArgumentException
     */
    public function fromString(string $string): object;
}
