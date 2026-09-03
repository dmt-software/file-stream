<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Parser;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use InvalidArgumentException;

final class JsonTemplateParser implements TemplateParserInterface
{
    private bool $placeholderReached = false;

    /**
     * @param resource $template
     * @param resource $stream
     */
    public function __construct(
        private readonly mixed $template,
        private readonly mixed $stream,
        private readonly string $placeholder = self::DEFAULT_PLACEHOLDER,
    ) {
        if (!is_resource($this->template)) {
            throw new InvalidArgumentException('Template must be a resource');
        }

        if (!is_resource($this->stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        if ($this->placeholder === '') {
            throw new InvalidArgumentException('Placeholder cannot be empty');
        }
    }

    /**
     * @inheritDoc
     */
    public function copyToPlaceholder(): void
    {
        if ($this->placeholderReached) {
            return;
        }

        $buffer = '';

        while (false !== ($char = fgetc($this->template))) {
            $buffer .= $char;

            if ($buffer === $this->placeholder) {
                $this->placeholderReached = true;

                return;
            }

            if (!str_starts_with($this->placeholder, $buffer)) {
                $this->write($buffer[0]);

                $buffer = substr($buffer, 1);
            }
        }

        throw new NotFoundException('Template placeholder not found');
    }

    /**
     * @inheritDoc
     */
    public function copyRemainder(): void
    {
        if (!$this->placeholderReached) {
            throw new ParserException('Template placeholder has not been reached');
        }

        while (!feof($this->template)) {
            $data = fread($this->template, 8192);

            if ($data === false) {
                throw new ParserException('Unable to read JSON template');
            }

            if ($data !== '') {
                $this->write($data);
            }
        }
    }

    private function write(string $data): void
    {
        $written = fwrite($this->stream, $data);

        if ($written === false || $written !== strlen($data)) {
            throw new ParserException('Unable to write JSON template');
        }
    }
}
