<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\WritableStreamInterface;

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
    public function __construct(private string $rootElement = 'Results')
    {
    }

    public function writePrefix(WritableStreamInterface $output): void
    {
        $output->write(sprintf('<?xml version="1.0" encoding="UTF-8"?>'));
        $output->write(sprintf('<%s>', $this->rootElement));
    }

    public function writeSuffix(WritableStreamInterface $output): void
    {
        $output->write(sprintf('</%s>', $this->rootElement));
    }
}
