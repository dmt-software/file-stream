<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\Selector\XmlElementPathSelector;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class XmlElementPathSelectorTest extends TestCase
{
    public function testSelectsRootElement(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser('elements.xml')
        );

        $node = $selector->moveToNode();

        $this->assertSame('root', $node->localName);
        $this->assertSame(1, $node->depth());
    }

    public function testSelectsElementByExactPath(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser('elements.xml'),
            path: '/root/group/element'
        );

        $node = $selector->moveToNode();

        $this->assertSame('element', $node->localName);
        $this->assertSame(3, $node->depth());
    }

    public function testSupportsWildcardPathSegment(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser('elements.xml'),
            path: '/root/./element'
        );

        $node = $selector->moveToNode();

        $this->assertSame('element', $node->localName);
        $this->assertSame(3, $node->depth());
    }

    #[DataProvider('malformedPathProvider')]
    public function testRejectsMalformedPath(string $path, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        new XmlElementPathSelector(
            parser: $this->parser('elements.xml'),
            path: $path
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function malformedPathProvider(): iterable
    {
        yield 'empty path' => ['', 'XML path cannot be empty'];
        yield 'missing leading slash' => ['root/group', 'Malformed XML path'];
        yield 'trailing slash' => ['/root/group/', 'Malformed XML path'];
        yield 'double slash' => ['/root//element', 'Malformed XML path'];
    }

    public function testThrowsWhenPathCannotBeFound(): void
    {
        $selector = new XmlElementPathSelector(
            parser: $this->parser('elements.xml'),
            path: '/root/missing'
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('End of file reached');

        $selector->moveToNode();
    }

    private function parser(string $fixture): Parser
    {
        $stream = fopen(dirname(__DIR__, 2) . '/fixtures/xml/' . $fixture, 'r');

        $this->assertIsResource($stream);

        return new Parser(new XmlReaderTokenizer(new StreamParser($stream)));
    }
}
