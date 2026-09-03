<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Parser;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;

interface TemplateParserInterface
{
    public const string DEFAULT_PLACEHOLDER = '{{items}}';

    /**
     * Copy the template up to, but not including, the configured placeholder.
     *
     * @throws NotFoundException
     * @throws ParserException
     */
    public function copyToPlaceholder(): void;

    /**
     * Copy the remainder of the template after the placeholder.
     *
     * @throws ParserException
     */
    public function copyRemainder(): void;
}
