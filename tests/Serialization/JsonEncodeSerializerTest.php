<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\JsonEncodeSerializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JsonEncodeSerializer::class)]
final class JsonEncodeSerializerTest extends TestCase
{
    public function testSerializesObject(): void
    {
        $object = new stdClass();
        $object->name = 'PHP';
        $object->since = 1995;

        $serializer = new JsonEncodeSerializer();

        $this->assertSame(
            '{"name":"PHP","since":1995}',
            $serializer->serialize($object)
        );
    }

    public function testSerializesNestedObject(): void
    {
        $author = new stdClass();
        $author->name = 'Rasmus Lerdorf';

        $object = new stdClass();
        $object->name = 'PHP';
        $object->author = $author;

        $serializer = new JsonEncodeSerializer();

        $this->assertSame(
            '{"name":"PHP","author":{"name":"Rasmus Lerdorf"}}',
            $serializer->serialize($object)
        );
    }

    public function testUsesJsonFlags(): void
    {
        $object = new stdClass();
        $object->url = 'https://www.php.net/';
        $object->name = 'PHP ©';

        $serializer = new JsonEncodeSerializer(
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $this->assertSame(
            '{"url":"https://www.php.net/","name":"PHP ©"}',
            $serializer->serialize($object)
        );
    }

    public function testAlwaysThrowsOnJsonEncodingError(): void
    {
        $object = new stdClass();
        $object->value = "\xB1\x31";

        $serializer = new JsonEncodeSerializer();

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageIs('Error encoding JSON data');

        $serializer->serialize($object);
    }
}
