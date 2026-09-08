<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Format\Xml\Reader\XmlElementPathSelector;
use DMT\FileStream\Format\Xml\XmlPath;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlElementPathSelector::class)]
final class XmlElementPathSelectorTest extends TestCase
{
    public function testSelectsRootElement(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser(),
            path: new XmlPath()
        );

        $node = $selector->moveToNode();

        $this->assertSame('root', $node->localName);
        $this->assertSame(1, $node->depth());
    }

    public function testSelectsElementByExactPath(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser(),
            path: new XmlPath('/root/group/element')
        );

        $node = $selector->moveToNode();

        $this->assertSame('element', $node->localName);
        $this->assertSame(3, $node->depth());
    }

    public function testSupportsWildcardPathSegment(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser(),
            path: new XmlPath('/root/./element')
        );

        $node = $selector->moveToNode();

        $this->assertSame('element', $node->localName);
        $this->assertSame(3, $node->depth());
    }

    public function testThrowsWhenPathCannotBeFound(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser(),
            path: new XmlPath('/root/missing')
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('End of file reached');

        $selector->moveToNode();
    }

    private function parser(): Parser
    {
        $stream = fopen(dirname(__DIR__, 2) . '/fixtures/xml/elements.xml', 'r');

        $this->assertIsResource($stream);

        return new Parser(new XmlReaderTokenizer(new StreamParser($stream)));
    }
}
