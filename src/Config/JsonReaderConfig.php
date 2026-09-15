<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

use DMT\FileStream\Structured\Path\DotSeparatedPath;
use DMT\FileStream\Structured\Path\PathInterface;

final readonly class JsonReaderConfig implements JsonConfigInterface
{
    public int $flags;

    public function __construct(
        int $flags = 0,
        public PathInterface $path = new DotSeparatedPath()
    ) {
        if ($flags & JSON_OBJECT_AS_ARRAY) {
            trigger_error(
                'JSON_OBJECT_AS_ARRAY is ignored because JSON decoding is configured to return objects',
                E_USER_WARNING,
            );

            $flags &= ~JSON_OBJECT_AS_ARRAY;
        }

        $this->flags = $flags;
    }
}