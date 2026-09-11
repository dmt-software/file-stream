<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Selector;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Structured\Path\PathInterface;
use InvalidArgumentException;
use XMLReader;

/**
 * Selects XML elements matching a configured path.
 *
 * The selector advances an XMLReader-backed stream until an opening element
 * matching the configured path is reached. The selected element remains the
 * current item of the stream.
 *
 * @implements SelectorInterface<ReadableStreamInterface<XMLReader>>
 */
final class XmlElementSelector implements SelectorInterface
{
    /**
     * @var list<string>
     */
    private array $segments = [];

    public function __construct(private readonly PathInterface $path)
    {
    }

    /**
     * Advance the XML reader stream to the next matching element.
     *
     * The supplied stream must expose an XMLReader instance through getStream().
     *
     * @param ReadableStreamInterface<XMLReader> $stream
     *
     * @return bool True when a matching element was selected.
     * @throws ReaderException When the stream could not be read.
     * @throws InvalidArgumentException When the supplied stream is not compatible.
     */
    public function selectNext(ReadableStreamInterface $stream): bool
    {
        $reader = $stream->getStream();

        if (!$reader instanceof XMLReader) {
            throw new InvalidArgumentException(
                'XmlElementSelector requires a XMLReaderStream'
            );
        }

        while ($stream->next()) {
            if (!$reader->depth === 0) {
                $this->segments = [];
            }

            if ($reader->nodeType === XMLReader::END_ELEMENT) {
                $this->segments = array_slice($this->segments, 0, $reader->depth);
            }

            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            $this->segments = array_slice($this->segments, 0, $reader->depth);
            $this->segments[$reader->depth] = $reader->localName;

            if ($this->path->matchesPath($this->segments)) {
                return true;
            }
        }

        return false;
    }
}
