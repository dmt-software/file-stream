<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

use DMT\FileStream\Structured\Path\PathInterface;
use DMT\FileStream\Structured\Path\SlashSeparatedPath;

final readonly class XmlReaderConfig implements XmlConfigInterface
{
    public function __construct(
        public int $flags = 0,
        public ?string $namespace = null,
        public PathInterface $path = new SlashSeparatedPath(),
    ) {
    }
}
