<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\WritableStreamInterface;
use LogicException;

/**
 * Writes template content around serialized values.
 *
 * Implementations may manage their own template source and insertion point.
 * Simple handlers can write directly to any compatible output stream, while
 * more complex handlers may require a specific writable stream implementation.
 */
interface TemplateHandlerInterface
{
    /**
     * Write the template content preceding the insertion point.
     *
     * @throws LogicException
     */
    public function writePrefix(WritableStreamInterface $output): void;

    /**
     * Write the remaining template content following the insertion point.
     *
     * @throws LogicException
     */
    public function writeSuffix(WritableStreamInterface $output): void;
}
