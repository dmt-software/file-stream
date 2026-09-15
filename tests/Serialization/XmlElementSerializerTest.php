<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Serialization\XmlElementSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(XmlElementSerializer::class)]
final class XmlElementSerializerTest extends TestCase
{
    public function testSerializeElement(): void
    {
        $serializer = new XmlElementSerializer();

        $element = new SimpleXMLElement('<item />');
        $element->addChild('name', 'John');

        $this->assertStringContainsString(
            '<item><name>John</name></item>',
            $serializer->serialize($element)
        );
    }

    public function testRemoveXmlDeclaration(): void
    {
        $serializer = new XmlElementSerializer();

        $element = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><item>value</item>'
        );

        $this->assertStringNotContainsString(
            '<?xml version="1.0" encoding="UTF-8"?>',
            $serializer->serialize($element)
        );
    }

    public function testSerializeNestedElements(): void
    {
        $serializer = new XmlElementSerializer();

        $element = new SimpleXMLElement('<item />');

        $user = $element->addChild('user');
        $user->addChild('name', 'John');

        $this->assertStringContainsString(
            '<item><user><name>John</name></user></item>',
            $serializer->serialize($element)
        );
    }

    public function testPreserveNamespaceDeclaration(): void
    {
        $serializer = new XmlElementSerializer();

        $element = new SimpleXMLElement('<x:item xmlns:x="urn:test" />');
        $element->addChild('x:name', 'John', 'urn:test');

        $this->assertStringContainsString(
            '<x:item xmlns:x="urn:test"><x:name>John</x:name></x:item>',
            $serializer->serialize($element)
        );
    }

    public function testRejectNonSimpleXmlElement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Expected SimpleXMLElement');

        $serializer = new XmlElementSerializer();
        $serializer->serialize(new stdClass());
    }
}
