<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Parser;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Format\Json\JsonPath;
use DMT\FileStream\Format\Json\Writer\JsonTemplateParser;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonTemplateParser::class)]
final class JsonTemplateParserTest extends TestCase
{
    public function testCopiesTemplateUpToPath(): void
    {
        $template = $this->stream('{"meta":{"version":1,"name":"test case"},"items":[]}');
        $output = fopen('php://temp', 'r+');

        $reader = new JsonReader();
        $reader->stream($template);

        $parser = new JsonTemplateParser($reader, new JsonPath('.items'), $output);
        $parser->copyToPath();

        rewind($output);

        $this->assertSame(
            '{"meta":{"version":1,"name":"test case"},"items":[',
            stream_get_contents($output)
        );
    }

    public function testCopiesRemainderAfterPath(): void
    {
        $template = $this->stream(
            '{"items":[],"meta":{"count":2,"status":"ok"},"done":true}'
        );
        $output = fopen('php://temp', 'r+');

        $reader = new JsonReader();
        $reader->stream($template);

        $parser = new JsonTemplateParser($reader, new JsonPath('.items'), $output);
        $parser->copyToPath();

        rewind($output);
        ftruncate($output, 0);

        $parser->copyRemainder();

        rewind($output);

        $this->assertSame(
            '],"meta":{"count":2,"status":"ok"},"done":true}',
            stream_get_contents($output)
        );
    }

    public function testThrowsWhenPathNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $template = $this->stream('{"items":[]}');

        $reader = new JsonReader();
        $reader->stream($template);

        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser($reader, new JsonPath('.item'),  $output);
        $parser->copyToPath();
    }

    public function testThrowsWhenCopyingRemainderBeforePath(): void
    {
        $this->expectException(ParserException::class);

        $template = $this->stream('{"items":[]}');

        $reader = new JsonReader();
        $reader->stream($template);

        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser($reader, new JsonPath('.items'), $output);
        $parser->copyRemainder();
    }

    public function testThrowsWhenOutputStreamCannotBeWritten(): void
    {
        $this->expectException(ParserException::class);

        $template = $this->stream('{"items":[{{items}}]}');

        $reader = new JsonReader();
        $reader->stream($template);

        $output = fopen('php://memory', 'r');

        $parser = new JsonTemplateParser($reader, new JsonPath('.items'), $output);
        @$parser->copyToPath();
    }

    /**
     * @return resource
     */
    private function stream(string $contents): mixed
    {
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }
}
