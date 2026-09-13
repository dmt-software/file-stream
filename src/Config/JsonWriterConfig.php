<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

use DMT\FileStream\Structured\Template\JsonArrayContainerHandler;
use DMT\FileStream\Structured\Template\TemplateHandlerInterface;

final readonly class JsonWriterConfig implements JsonConfigInterface
{
    public function __construct(
        public int $flags = 0,
        public TemplateHandlerInterface $template = new JsonArrayContainerHandler(),
    ) {
    }
}
