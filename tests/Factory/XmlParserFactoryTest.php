<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Factory;

use DMT\FileStream\Factory\XmlParserFactory;
use DMT\XmlParser\Node\Element;
use DMT\XmlParser\Parser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class XmlParserFactoryTest extends TestCase
{
    public const string FILE = __DIR__ . '/../Fixtures/xml/programming.xml';

    public function testFromString(): void
    {
        $parser = new XmlParserFactory()
            ->fromString(file_get_contents(self::FILE));

        $this->assertInstanceOf(Element::class, $parser->parse());
    }

    public function testFromStream(): void
    {
        $parser = new XmlParserFactory()
            ->fromStream(fopen(self::FILE, 'r'));

        $this->assertInstanceOf(Element::class, $parser->parse());
    }

    public function testFromFile(): void
    {
        $parser = new XmlParserFactory()->fromFile(self::FILE);

        $this->assertInstanceOf(Element::class, $parser->parse());
    }

    public function testFromStreamRejectsNonResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new XmlParserFactory()->fromStream('not-a-resource');
    }

    public function testFromFileRejectsMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new XmlParserFactory()->fromFile(
            dirname(__DIR__) . '/../Fixtures/xml/missing.xml'
        );
    }
}
