<?php

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Structured\Template\TemplateHandlerInterface;

/**
 * Writes a JSON array container around serialized values.
 *
 * The handler writes the opening array before the values are emitted and the
 * closing array afterwards. The values themselves are supplied by the caller
 * and are not interpreted or validated by this handler.
 */
final class JsonArrayContainerHandler implements TemplateHandlerInterface
{
    public function writePrefix(WritableStreamInterface $output): void
    {
        $output->write('[');
    }

    public function writeSuffix(WritableStreamInterface $output): void
    {
        $output->write(']');
    }
}
