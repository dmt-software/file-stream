<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\Exception;

interface ResourceInterface
{
    /**
     * Get the stream resource.
     *
     * @return resource
     * @throws Exception When the stream is not a resource or closed.
     */
    public function getResource(): mixed;
}
