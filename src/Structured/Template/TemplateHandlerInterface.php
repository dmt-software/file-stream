<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\WritableStreamInterface;

/**
 * Writes structured template content around an insertion point.
 *
 * Implementations manage their own template source and insertion point. The
 * template prefix and suffix are written to a supplied output stream so
 * serialized values can be written between them.
 */
interface TemplateHandlerInterface
{
    /**
     * Write the template content preceding the insertion point.
     */
    public function writePrefix(WritableStreamInterface $output): void;

    /**
     * Write the remaining template content following the insertion point.
     *
     * This method must be called after writePrefix() on the same handler instance.
     */
    public function writeSuffix(WritableStreamInterface $output): void;
}
