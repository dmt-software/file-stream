<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Stream;

interface FinalizeStreamInterface
{
    public function finalize(): void;
}
