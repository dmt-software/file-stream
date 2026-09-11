<?php

declare(strict_types=1);

namespace DMT\FileStream\Config;

final readonly class JsonConfig
{
    public int $flags;

    public function __construct(int $flags = 0)
    {
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
