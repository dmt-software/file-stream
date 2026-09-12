<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\WritableStreamInterface;
use LogicException;

/**
 * Writes XML template content around a configured insertion point.
 *
 * The configured path identifies the element that will contain the serialized
 * values written between the template prefix and suffix. The values themselves
 * are supplied by the caller and are not interpreted or validated by this
 * handler.
 */
final class XmlRootElementTemplateHandler implements TemplateHandlerInterface
{
    /**
     * Indicates if the prefix has already been written.
     */
    private bool $prefixWritten = false;

    public function __construct(private string $rootElement = 'Results')
    {
    }

    public function writePrefix(WritableStreamInterface $output): void
    {
        $this->prefixWritten = true;

        $output->write('<?xml version="1.0" encoding="UTF-8"?>');
        $output->write(sprintf('<%s>', $this->rootElement));
        $output->flush();
    }

    public function writeSuffix(WritableStreamInterface $output): void
    {
        if (!$this->prefixWritten) {
            throw new LogicException('XML template prefix was not written');
        }

        $output->write(sprintf('</%s>', $this->rootElement));
    }
}
