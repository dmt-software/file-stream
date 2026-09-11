<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

final readonly class XmlConfig
{
    public function __construct(
        public int $flags = 0,
        public ?string $namespace = null,
    ) {
    }
}
