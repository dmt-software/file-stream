<?php

declare(strict_types=1);

namespace DMT\FileStream\Exception;

use Throwable;

/**
 * Marks exceptions thrown by the file-stream package.
 *
 * All package-specific exceptions implement this interface, so callers can
 * catch failures originating from the package without depending on a
 * concrete exception type.
 */
interface Exception extends Throwable
{

}
