<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer\XmlParserTokenizer;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;
use InvalidArgumentException;

/**
 * @implements ParserFactoryInterface<Parser>
 */
final readonly class XmlParserFactory implements ParserFactoryInterface
{
    /**
     * @param array{"encoding"?: string, flags?: int} $config
     */
    public function __construct(
        private readonly array $config = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fromFile(string $filename): Parser
    {
        return new Parser(
            new XmlReaderTokenizer(
                new FileParser($filename),
                ...$this->config
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function fromString(string $string): Parser
    {
        return new Parser(
            new XmlParserTokenizer(
                new StringParser($string),
                ...$this->config
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function fromStream(mixed $stream): Parser
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid stream');
        }

        return new Parser(
            new XmlParserTokenizer(
                new StreamParser($stream),
                ...$this->config
            )
        );
    }
}
