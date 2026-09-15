<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Selector;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Stream\ReadableStreamInterface;
use InvalidArgumentException;

/**
 * Selects logical items from a readable stream.
 *
 * A selector advances the supplied stream until the next matching item is
 * reached. The selected item remains available through the stream's current
 * state after this method returns successfully.
 *
 * Selectors may apply format-specific rules such as path matching, element
 * selection or logical record boundary detection.
 *
 * @template T of ReadableStreamInterface
 */
interface SelectorInterface
{
    /**
     * Advance the stream to the next matching item.
     *
     * The supplied stream must be compatible with the selector implementation.
     * Implementations may require a specific readable stream type in order to
     * access format-specific parser state through the stream.
     *
     * @param T $stream The compatible readable stream to select from.
     *
     * @return bool True when an item was selected.
     * @throws ReaderException When the stream could not be read.
     * @throws InvalidArgumentException When the supplied stream is not compatible.
     */
    public function selectNext(ReadableStreamInterface $stream): bool;
}
