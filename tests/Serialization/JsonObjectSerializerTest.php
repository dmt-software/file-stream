<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Config\JsonWriterConfig;
use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\JsonObjectSerializer;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JsonObjectSerializer::class)]
final class JsonObjectSerializerTest extends TestCase
{
    public function testSerializeJsonObject(): void
    {
        $serializer = new JsonObjectSerializer(new JsonWriterConfig());

        $object = new stdClass();
        $object->name = 'John';
        $object->age = 42;

        $this->assertSame('{"name":"John","age":42}', $serializer->serialize($object));
    }

    public function testSerializeNestedJsonObject(): void
    {
        $serializer = new JsonObjectSerializer(new JsonWriterConfig());

        $object = new stdClass();
        $object->user = new stdClass();
        $object->user->name = 'John';

        $this->assertSame('{"user":{"name":"John"}}', $serializer->serialize($object));
    }

    public function testUseConfiguredFlags(): void
    {
        $serializer = new JsonObjectSerializer(
            new JsonWriterConfig(JSON_PRETTY_PRINT)
        );

        $object = new stdClass();
        $object->name = 'John';

        $this->assertSame(
            <<<'JSON'
            {
                "name": "John"
            }
            JSON,
            $serializer->serialize($object)
        );
    }

    public function testPreserveUnicodeUsingConfiguredFlags(): void
    {
        $serializer = new JsonObjectSerializer(
            new JsonWriterConfig(JSON_UNESCAPED_UNICODE)
        );

        $object = new stdClass();
        $object->name = 'José';

        $this->assertSame(
            '{"name":"José"}',
            $serializer->serialize($object)
        );
    }

    public function testWrapJsonException(): void
    {
        $serializer = new JsonObjectSerializer(new JsonWriterConfig());

        $object = new stdClass();
        $object->value = NAN;

        try {
            $serializer->serialize($object);

            $this->fail('SerializationException was not thrown');
        } catch (SerializationException $exception) {
            $this->assertSame('Error encoding JSON data', $exception->getMessage());
            $this->assertInstanceOf(JsonException::class, $exception->getPrevious());
        }
    }
}
