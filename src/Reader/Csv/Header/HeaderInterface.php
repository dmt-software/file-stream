<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv\Header;

interface HeaderInterface
{
    public function getHeader(): array;
}
