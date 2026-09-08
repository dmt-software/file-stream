<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

interface PrepareStreamInterface
{
    public function prepare(): void;
}
