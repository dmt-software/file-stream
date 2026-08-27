<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Parser;

use Stringable;

final class JsonObjectNode implements Stringable
{
    public function __construct(
        public int $depth = 0,
        public ?string $name = null,
        public ?string $value = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
