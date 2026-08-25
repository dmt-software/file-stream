<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Parser;

use Stringable;

final class JsonObjectNode implements Stringable
{
    public function __construct(
        public int $depth = 0,
        public ?string $name = null,
        public ?array $value = null,
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->value, JSON_THROW_ON_ERROR);
    }
}
