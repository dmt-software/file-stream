<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader;

use DMT\FileStream\Config\XmlReaderConfig;
use DMT\FileStream\Reader\XmlReader;
use DMT\FileStream\Stream\XmlReaderStream;
use DMT\FileStream\Structured\Path\SlashSeparatedPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

#[CoversClass(XmlReader::class)]
#[Group('integration')]
final class XmlReaderTest extends TestCase
{
    public function testReadXmlElements(): void
    {
        $resource = fopen(
            __DIR__ . '/../fixtures/xml/people.xml',
            'r'
        );

        $this->assertIsResource($resource);

        $reader = new XmlReader(
            new XmlReaderStream($resource),
            new XmlReaderConfig(
                path: new SlashSeparatedPath('/people/person')
            )
        );

        $results = iterator_to_array($reader->getResults());

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(
            SimpleXMLElement::class,
            $results
        );

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => '42',
            ],
            array_map(strval(...), iterator_to_array($results[0]->children()))
        );

        $this->assertSame(
            [
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'age' => '35',
            ],
            array_map(strval(...), iterator_to_array($results[1]->children()))
        );
    }
}
