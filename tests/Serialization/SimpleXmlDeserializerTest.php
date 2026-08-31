<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\SimpleXmlDeserializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

#[CoversClass(SimpleXmlDeserializer::class)]
final class SimpleXmlDeserializerTest extends TestCase
{
    public function testDeserializesXmlElement(): void
    {
        $deserializer = new SimpleXmlDeserializer();

        $result = $deserializer->deserialize(
            '<language><name>PHP</name><since>1995</since></language>'
        );

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertSame('PHP', (string) $result->name);
        $this->assertSame('1995', (string) $result->since);
    }

    public function testUsesLibxmlOptions(): void
    {
        $deserializer = new SimpleXmlDeserializer(
            options: LIBXML_NOCDATA
        );

        $result = $deserializer->deserialize(
            '<language><name><![CDATA[PHP]]></name></language>'
        );

        $this->assertSame('PHP', (string) $result->name);
    }

    public function testUsesNamespace(): void
    {
        $deserializer = new SimpleXmlDeserializer(
            namespace: 'urn:language'
        );

        $result = $deserializer->deserialize(
            '<language xmlns="urn:language"><name>PHP</name></language>'
        );

        $this->assertSame('PHP', (string) $result->name);
    }

    #[DataProvider('invalidDataProvider')]
    public function testRejectsInvalidData(string $data, string $message): void
    {
        $deserializer = new SimpleXmlDeserializer();

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageIs($message);

        @$deserializer->deserialize($data);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidDataProvider(): iterable
    {
        yield 'empty string' => [
            '',
            'Invalid XML data',
        ];

        yield 'plain text' => [
            'PHP',
            'Invalid XML data',
        ];

        yield 'malformed xml' => [
            '<language>',
            'Error deserializing XML data',
        ];

        yield 'multiple roots' => [
            '<one/><two/>',
            'Error deserializing XML data',
        ];
    }
}
