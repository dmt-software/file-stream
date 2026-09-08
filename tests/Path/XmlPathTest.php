<?php

namespace DMT\Test\FileStream\Path;

use DMT\FileStream\Format\Xml\XmlPath;
use DMT\XmlParser\Node\Element;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class XmlPathTest extends TestCase
{
    public function testGetSegments(): void
    {
        $this->assertSame(['root', 'elem'], new XmlPath('/root/elem')->getSegments());
    }

    #[DataProvider('pathProvider')]
    public function testMatchesPath(string $path, array $nodes): void
    {
        $this->assertTrue(new XmlPath($path)->matchesPath($nodes));
    }

    public static function pathProvider(): iterable
    {
        yield 'default path' => ['/.', [new Element('root')]];
        yield 'literal path' => ['/root/elem', [new Element('root'), new Element('elem')]];
        yield 'wildcard path' => ['/./elem', [new Element('root'), new Element('elem')]];
    }

    public function testDoesNotMatchesPath(): void
    {
        $this->assertFalse(new XmlPath('/root/elem')->matchesPath([new Element('other')]));
    }

    #[DataProvider('malformedPathProvider')]
    public function testRejectsMalformedPath(string $path, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        new XmlPath($path);
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
}
