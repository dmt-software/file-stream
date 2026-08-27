<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use DMT\FileStream\Selector\JsonPathSelector;
use InvalidArgumentException;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonPathSelectorTest extends TestCase
{
    public function testMovesToRootPath(): void
    {
        $parser = $this->parser('root-array.json');

        $selector = new JsonPathSelector($parser);
        $selector->moveTo('.');

        $node = $parser->parseValue();

        $this->assertNull($node->name);
    }

    public function testMovesToNestedPath(): void
    {
        $parser = $this->parser('objects.json');

        $selector = new JsonPathSelector($parser);
        $selector->moveTo('.meta');

        $node = $parser->parseValue();

        $this->assertSame('meta', $node->name);
        $this->assertSame('{"name":"programming languages","count":2}',
            $node->value
        );
    }

    public function testMovesToArrayPath(): void
    {
        $parser = $this->parser('objects.json');
        $selector = new JsonPathSelector($parser);

        $selector->moveTo('.languages');

        $node = $parser->parseValue();

        $this->assertSame('languages', $node->name);
        $this->assertSame(
            '{"name":"JavaScript","since":1995,"author":{"name":"Brendan Eich"}}',
            $node->value
        );
    }

    public function testSupportsEscapedDotInPropertyName(): void
    {
        $parser = $this->parser('escaped-dot.json');
        $selector = new JsonPathSelector($parser);

        $selector->moveTo('.response\.data.languages');

        $node = $parser->parseValue();

        $this->assertSame('languages', $node->name);
        $this->assertSame('{"name":"JavaScript"}', $node->value);
    }

    #[DataProvider('malformedPathProvider')]
    public function testRejectsMalformedPath(string $path): void
    {
        $selector = new JsonPathSelector(
            $this->parser('objects.json')
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Malformed JSON path');

        $selector->moveTo($path);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function malformedPathProvider(): iterable
    {
        yield 'missing leading dot' => ['meta'];
        yield 'trailing dot' => ['.meta.'];
        yield 'empty segment' => ['.meta..name'];
        yield 'empty path' => [''];
    }

    public function testThrowsWhenPathCannotBeFound(): void
    {
        $selector = new JsonPathSelector(
            $this->parser('objects.json')
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('JSON path not found');

        $selector->moveTo('.missing');
    }

    private function parser(string $fixture): JsonObjectNodeParser
    {
        $reader = new JsonReader();
        $reader->stream(fopen(dirname(__DIR__) . '/fixtures/json/' . $fixture, 'r'));

        return new JsonObjectNodeParser($reader);
    }
}
