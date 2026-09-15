<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\JsonWriterStream;
use DMT\FileStream\Stream\WritableStreamInterface;
use LogicException;

/**
 * Writes a JSON array container around serialized values.
 *
 * The handler writes the opening array before the values are emitted and the
 * closing array afterwards. The values themselves are supplied by the caller
 * and are not interpreted or validated by this handler.
 *
 * @implements TemplateHandlerInterface<JsonWriterStream>
 */
final class JsonArrayContainerHandler implements TemplateHandlerInterface
{
    /**
     * Indicates if the prefix has already been written.
     */
    private bool $prefixWritten = false;

    /**
     * @inheritDoc
     */
    public function writePrefix(WritableStreamInterface $output): void
    {
        if (!$output instanceof JsonWriterStream) {
            throw new LogicException('JSON template requires a JsonWriterStream');
        }

        if ($this->prefixWritten) {
            throw new LogicException('JSON template prefix was already written');
        }

        $this->prefixWritten = true;

        fwrite($output->getStream(), '[');

        $output->flush();
    }

    public function writeSuffix(WritableStreamInterface $output): void
    {
        if (!$output instanceof JsonWriterStream) {
            throw new LogicException('JSON template requires a JsonWriterStream');
        }

        if (!$this->prefixWritten) {
            throw new LogicException('JSON template prefix was not written');
        }

        fwrite($output->getStream(), ']');

        $output->flush();
    }
}
