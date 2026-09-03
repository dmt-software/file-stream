<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Serialization\SimpleXmlSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(SimpleXmlSerializer::class)]
final class SimpleXmlSerializerTest extends TestCase
{
    public function testSerializesSimpleXmlElementWithoutDeclaration(): void
    {
        $serializer = new SimpleXmlSerializer();

        $xml = new SimpleXMLElement(
            '<item><name>John</name></item>'
        );

        $this->assertSame(
            '<item><name>John</name></item>',
            trim($serializer->serialize($xml))
        );
    }

    public function testPreservesAttributesAndNestedElements(): void
    {
        $serializer = new SimpleXmlSerializer();

        $xml = new SimpleXMLElement(
            '<item id="10"><name>John</name><active>1</active></item>'
        );

        $this->assertSame(
            '<item id="10"><name>John</name><active>1</active></item>',
            trim($serializer->serialize($xml))
        );
    }

    public function testDoesNotReturnXmlDeclaration(): void
    {
        $serializer = new SimpleXmlSerializer();

        $xml = new SimpleXMLElement('<item>value</item>');

        $this->assertStringNotContainsString(
            '<?xml',
            $serializer->serialize($xml)
        );
    }

    public function testRejectsUnsupportedObjectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Expected SimpleXMLElement');

        $serializer = new SimpleXmlSerializer();
        $serializer->serialize(new stdClass());
    }

    public function testThrowsWhenSimpleXmlCannotBeSerialized(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageIs('Error encoding XML data');

        $xml = new class('<item/>') extends SimpleXMLElement {
            public function asXML(?string $filename = null): string|bool
            {
                return false;
            }
        };

        $serializer = new SimpleXmlSerializer();
        $serializer->serialize($xml);
    }
}
