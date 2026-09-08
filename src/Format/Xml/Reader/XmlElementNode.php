<?php

declare(strict_types = 1);

namespace DMT\FileStream\Format\Xml\Reader;

use Stringable;

final class XmlElementNode implements Stringable
{
    public function __construct(
        public readonly int $depth,
        public readonly string $name,
        public ?string $value = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }
}
