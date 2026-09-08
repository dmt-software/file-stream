<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

interface FinalizeStreamInterface
{
    public function finalize(): void;
}
