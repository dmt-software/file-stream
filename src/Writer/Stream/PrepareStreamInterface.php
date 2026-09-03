<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Stream;

interface PrepareStreamInterface
{
    public function prepare(): void;
}
