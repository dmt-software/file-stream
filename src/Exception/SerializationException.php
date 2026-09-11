<?php

declare(strict_types=1);

namespace DMT\FileStream\Exception;

use RuntimeException;

/**
 * Represents a failure while serializing or deserializing data.
 *
 * This exception is used when an object cannot be converted into its
 * serialized representation, or when serialized input cannot be converted
 * into the expected object representation.
 */
class SerializationException extends RuntimeException implements Exception
{

}
