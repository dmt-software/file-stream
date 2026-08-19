<?php

declare(strict_types=1);

namespace DMT\FileStream\Factory;

use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer\XmlParserTokenizer;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;

/**
 * @implements ParserFactoryInterface<Parser>
 */
class XmlParserFactory implements ParserFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function fromFile(string $filename): Parser
    {
        return new Parser(new XmlReaderTokenizer(new FileParser($filename)));
    }

    /**
     * @inheritDoc
     */
    public function fromString(string $string): Parser
    {
        return new Parser(new XmlParserTokenizer(new StringParser($string)));
    }

    /**
     * @inheritDoc
     */
    public function fromStream(mixed $stream): Parser
    {
        return new Parser(new XmlParserTokenizer(new StreamParser($stream)));
    }
}
