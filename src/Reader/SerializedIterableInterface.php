<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use IteratorAggregate;

/**
 * Iterates over serialized string values.
 *
 * Implementations may produce values from record-based or structured input
 * but always expose complete serialized units for further processing by an
 * object reader.
 *
 * @extends IteratorAggregate<int, string>
 */
interface SerializedIterableInterface extends IteratorAggregate
{
}
