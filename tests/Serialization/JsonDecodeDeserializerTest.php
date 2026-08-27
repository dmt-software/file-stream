<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\JsonDecodeDeserializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class JsonDecodeDeserializerTest extends TestCase
{
    public function testDeserializesJsonObject(): void
    {
        $deserializer = new JsonDecodeDeserializer();

        $result = $deserializer->deserialize(
            '{"name":"PHP","since":1995}'
        );

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertSame('PHP', $result->name);
        $this->assertSame(1995, $result->since);
    }

    public function testPassesFlagsToJsonDecode(): void
    {
        $deserializer = new JsonDecodeDeserializer(
            JSON_BIGINT_AS_STRING
        );

        $result = $deserializer->deserialize(
            '{"value":9223372036854775808}'
        );

        $this->assertSame(
            '9223372036854775808',
            $result->value
        );
    }

    public function testRejectsJsonObjectAsArrayFlag(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            'JSON_OBJECT_AS_ARRAY is not supported.'
        );

        new JsonDecodeDeserializer(
            JSON_OBJECT_AS_ARRAY
        );
    }

    #[DataProvider('invalidJsonProvider')]
    public function testRejectsInvalidJson(string $data): void
    {
        $deserializer = new JsonDecodeDeserializer();

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageIs('Invalid JSON object');

        $deserializer->deserialize($data);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidJsonProvider(): iterable
    {
        yield 'array' => ['[{"name":"PHP"}]'];
        yield 'string' => ['"PHP"'];
        yield 'number' => ['1995'];
        yield 'null' => ['null'];
        yield 'leading whitespace' => [' {"name":"PHP"}'];
        yield 'malformed object' => ['{"name":"PHP"'];
        yield 'empty string' => [''];
        yield 'invalid utf8' => ["{\"name\":\"PH\x80P\"}"];
    }

    public function testCanIgnoreInvalidUtf8DuringValidationAndDecoding(): void
    {
        $deserializer = new JsonDecodeDeserializer(
            JSON_INVALID_UTF8_IGNORE
        );

        $result = $deserializer->deserialize(
            "{\"name\":\"PH\x80P\"}"
        );

        $this->assertSame('PHP', $result->name);
    }
}
