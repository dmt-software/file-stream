<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Parser;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Writer\Parser\JsonTemplateParser;
use DMT\FileStream\Writer\Parser\TemplateParserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonTemplateParser::class)]
final class JsonTemplateParserTest extends TestCase
{
    public function testCopiesTemplateUpToPlaceholder(): void
    {
        $template = $this->stream('{"meta":{"version":1},"items":[{{items}}]}');
        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser($template, $output);
        $parser->copyToPlaceholder();

        rewind($output);

        $this->assertSame(
            '{"meta":{"version":1},"items":[',
            stream_get_contents($output)
        );
    }

    public function testCopiesRemainderAfterPlaceholder(): void
    {
        $template = $this->stream(
            '{"items":[{{items}}],"meta":{"count":2,"status":"ok"},"done":true}'
        );
        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser($template, $output);
        $parser->copyToPlaceholder();

        rewind($output);
        ftruncate($output, 0);

        $parser->copyRemainder();

        rewind($output);

        $this->assertSame(
            '],"meta":{"count":2,"status":"ok"},"done":true}',
            stream_get_contents($output)
        );
    }

    public function testUsesCustomPlaceholder(): void
    {
        $template = $this->stream('{"items":[__DATA__]}');
        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser(
            template: $template,
            stream: $output,
            placeholder: '__DATA__'
        );

        $parser->copyToPlaceholder();
        $parser->copyRemainder();

        rewind($output);

        $this->assertSame('{"items":[]}', stream_get_contents($output));
    }

    public function testThrowsWhenPlaceholderDoesNotExist(): void
    {
        $this->expectException(NotFoundException::class);

        $template = $this->stream('{"items":[]}');
        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser($template, $output);
        $parser->copyToPlaceholder();
    }

    public function testThrowsWhenCopyingRemainderBeforePlaceholder(): void
    {
        $this->expectException(ParserException::class);

        $template = $this->stream('{"items":[{{items}}]}');
        $output = fopen('php://temp', 'r+');

        $parser = new JsonTemplateParser($template, $output);
        $parser->copyRemainder();
    }

    public function testThrowsWhenOutputStreamCannotBeWritten(): void
    {
        $this->expectException(ParserException::class);

        $template = $this->stream('{"items":[{{items}}]}');
        $output = fopen('php://memory', 'r');

        $parser = new JsonTemplateParser($template, $output);
        @$parser->copyToPlaceholder();
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
