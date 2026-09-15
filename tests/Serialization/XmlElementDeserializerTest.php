<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Config\XmlReaderConfig;
use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\XmlElementDeserializer;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

#[CoversClass(XmlElementDeserializer::class)]
final class XmlElementDeserializerTest extends TestCase
{
    public function testDeserializeXmlElement(): void
    {
        $deserializer = new XmlElementDeserializer(new XmlReaderConfig());

        $result = $deserializer->deserialize('<item><name>John</name></item>');

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertSame('John', (string)$result->name);
    }

    public function testDeserializeNestedXmlElement(): void
    {
        $deserializer = new XmlElementDeserializer(new XmlReaderConfig());

        $result = $deserializer->deserialize('<item><user><name>John</name></user></item>');

        $this->assertSame('John', (string)$result->user->name);
    }

    public function testUseConfiguredNamespace(): void
    {
        $deserializer = new XmlElementDeserializer(
            new XmlReaderConfig(namespace: 'urn:x')
        );

        $result = $deserializer->deserialize(
            '<root xmlns="urn:def" xmlns:x="urn:x"><x:item>value</x:item></root>'
        );

        $this->assertSame('value', (string)$result->item);
    }

    #[DataProvider('provideInvalidXml')]
    public function testRejectInvalidXmlElement(string $xml): void
    {
        $this->expectException(SerializationException::class);

        $deserializer = new XmlElementDeserializer(new XmlReaderConfig());
        @$deserializer->deserialize($xml);
    }

    public function testRejectDataThatDoesNotStartWithElement(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageIs('Invalid XML element');

        $deserializer = new XmlElementDeserializer(new XmlReaderConfig());
        $deserializer->deserialize(' <item />');
    }

    public function testWrapSimpleXmlFailure(): void
    {
        $deserializer = new XmlElementDeserializer(new XmlReaderConfig());

        try {
            @$deserializer->deserialize('<item>');

            $this->fail('SerializationException was not thrown');
        } catch (SerializationException $exception) {
            $this->assertSame('Unable to deserialize XML element', $exception->getMessage());
            $this->assertInstanceOf(Exception::class, $exception->getPrevious());
        }
    }

    public static function provideInvalidXml(): iterable
    {
        return [
            'empty' => [''],
            'text' => ['value'],
            'json' => ['{"name":"John"}'],
            'unclosed element' => ['<item>'],
            'mismatched element' => ['<item></other>'],
        ];
    }
}
