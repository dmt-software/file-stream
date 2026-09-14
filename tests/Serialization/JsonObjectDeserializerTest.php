<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Config\JsonReaderConfig;
use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\JsonObjectDeserializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JsonObjectDeserializer::class)]
final class JsonObjectDeserializerTest extends TestCase
{
    public function testDeserializeJsonObject(): void
    {
        $deserializer = new JsonObjectDeserializer(new JsonReaderConfig());

        $result = $deserializer->deserialize('{"name":"John","age":42}');

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertSame('John', $result->name);
        $this->assertSame(42, $result->age);
    }

    public function testDeserializeNestedJsonObject(): void
    {
        $deserializer = new JsonObjectDeserializer(new JsonReaderConfig());

        $result = $deserializer->deserialize('{"user":{"name":"John"}}');

        $this->assertInstanceOf(stdClass::class, $result->user);
        $this->assertSame('John', $result->user->name);
    }

    #[DataProvider('provideInvalidJsonObjects')]
    public function testRejectInvalidJsonObject(string $data): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageIs('Invalid JSON object');

        $deserializer = new JsonObjectDeserializer(new JsonReaderConfig());
        $deserializer->deserialize($data);
    }

    public function testUseConfiguredDecodeFlags(): void
    {
        $deserializer = new JsonObjectDeserializer(
            new JsonReaderConfig(JSON_BIGINT_AS_STRING)
        );

        $result = $deserializer->deserialize('{"value":9223372036854775808}');

        $this->assertSame('9223372036854775808', $result->value);
    }

    public static function provideInvalidJsonObjects(): iterable
    {
        return [
            'empty' => [''],
            'array' => ['[]'],
            'string' => ['"value"'],
            'integer' => ['42'],
            'invalid object' => ['{"name":}'],
            'leading whitespace' => [' {"name":"John"}'],
        ];
    }
}