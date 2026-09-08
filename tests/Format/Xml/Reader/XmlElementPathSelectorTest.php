<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Format\Xml\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Format\Xml\Reader\XmlElementNodeParser;
use DMT\FileStream\Format\Xml\Reader\XmlElementPathSelector;
use DMT\FileStream\Format\Xml\XmlPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use XmlReader;

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

        $this->assertSame('root', $node->name);
        $this->assertSame(0, $node->depth);
    }

    public function testSelectsElementByExactPath(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser(),
            path: new XmlPath('/root/group/element')
        );

        $node = $selector->moveToNode();

        $this->assertSame('element', $node->name);
        $this->assertSame(2, $node->depth);
    }

    public function testSupportsWildcardPathSegment(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser(),
            path: new XmlPath('/root/./element')
        );

        $node = $selector->moveToNode();

        $this->assertSame('element', $node->name);
        $this->assertSame(2, $node->depth);
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

    private function parser(): XmlElementNodeParser
    {
        $stream = fopen(dirname(__DIR__, 3) . '/fixtures/xml/elements.xml', 'r');

        $this->assertIsResource($stream);

        return new XmlElementNodeParser(XmlReader::fromStream($stream));
    }
}
