<?php

namespace DMT\FileStream\Config;

use DMT\FileStream\Structured\Template\TemplateHandlerInterface;
use DMT\FileStream\Structured\Template\XmlRootElementTemplateHandler;

final readonly class XmlWriterConfig
{
    public function __construct(
        public TemplateHandlerInterface $template = new XmlRootElementTemplateHandler(),
    ) {
    }
}
