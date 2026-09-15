<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

interface XmlConfigInterface
{
    public int $flags { get; }
    public ?string $namespace { get; }
}
