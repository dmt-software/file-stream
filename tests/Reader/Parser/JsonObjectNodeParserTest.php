<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Parser;

use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;

final class JsonObjectNodeParserTest extends TestCase
{
    public function testParsesRootObject(): void
    {
        $parser = $this->parser('objects.json');

        $node = $parser->parse();

        $this->assertNotNull($node);
        $this->assertSame(0, $node->depth);
        $this->assertNull($node->name);
        $this->assertNull($node->value);
    }

    public function testParsesNamedNestedObject(): void
    {
        $parser = $this->parser('objects.json');

        $parser->parse();
        $node = $parser->parse();

        $this->assertNotNull($node);
        $this->assertSame(1, $node->depth);
        $this->assertSame('meta', $node->name);
        $this->assertNull($node->value);
    }

    public function testUsesArrayNameForObjectsInsideArray(): void
    {
        $parser = $this->parser('objects.json');

        while ($node = $parser->parse()) {
            if ($node->depth === 1 && $node->name === 'languages') {
                $this->assertSame('languages', $node->name);

                return;
            }
        }

        $this->fail('Could not find language node.');
    }

    public function testParsesNestedObjectInsideArrayObject(): void
    {
        $parser = $this->parser('objects.json');

        while ($node = $parser->parse()) {
            if ($node->name === 'author') {
                $this->assertSame(2, $node->depth);

                return;
            }
        }

        $this->fail('Could not find author node.');
    }

    public function testAppliesValueToCurrentNode(): void
    {
        $parser = $this->parser('objects.json');

        $parser->parse();
        $parser->parse();

        $node = $parser->parseValue();

        $this->assertSame('meta', $node->name);
        $this->assertSame(
            '{"name":"programming languages","count":2}',
            $node->value
        );
    }

    public function testApplyingValueWithoutCurrentNodeThrowsException(): void
    {
        $parser = $this->parser('objects.json');

        $this->expectException(ParserException::class);
        $this->expectExceptionMessageIs('No current JSON object');

        $parser->parseValue();
    }

    public function testReturnsNullAtEndOfInput(): void
    {
        $parser = $this->parser('objects.json');

        while (($node = $parser->parse()) !== null) {
            // skip to the next object
        }

        $this->assertNull($node);
    }

    public function testInvalidJsonThrowsParserException(): void
    {
        $parser = $this->parser('invalid.json');

        $this->expectException(ParserException::class);
        $this->expectExceptionMessageIs('Error parsing JSON');

        while ($parser->parse() !== null) {
            // skip to the next object
        }
    }

    private function parser(string $fixture): JsonObjectNodeParser
    {
        $reader = new JsonReader();
        $reader->stream(fopen(dirname(__DIR__, 2) . '/fixtures/json/' . $fixture, 'r'));

        return new JsonObjectNodeParser($reader);
    }
}
