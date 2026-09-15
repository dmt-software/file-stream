<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer;

use DMT\FileStream\Config\XmlWriterConfig;
use DMT\FileStream\Stream\XmlWriterStream;
use DMT\FileStream\Structured\Template\XmlRootElementTemplateHandler;
use DMT\FileStream\Writer\XmlWriter;
use PHPUnit\Event\Runtime\PHP;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

#[CoversClass(XmlWriter::class)]
#[Group('integration')]
final class XmlWriterTest extends TestCase
{
    private const string OUTPUT_FILE = __DIR__ . '/../fixtures/xml/output.xml';

    protected function tearDown(): void
    {
        $handle = fopen(self::OUTPUT_FILE, 'w');

        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    public function testWriteXmlElements(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new XmlWriter(
            new XmlWriterStream($resource),
            new XmlWriterConfig(
                template: new XmlRootElementTemplateHandler('People')
            )
        );

        $first = new SimpleXMLElement('<Person />');
        $first->addChild('firstName', 'John');
        $first->addChild('lastName', 'Doe');
        $first->addChild('age', '42');

        $second = new SimpleXMLElement('<Person />');
        $second->addChild('firstName', 'Jane');
        $second->addChild('lastName', 'Smith');
        $second->addChild('age', '35');

        $writer->write([$first, $second]);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . PHP_EOL . '<People>'
            . PHP_EOL . '<Person><firstName>John</firstName><lastName>Doe</lastName><age>42</age></Person>'
            . PHP_EOL . '<Person><firstName>Jane</firstName><lastName>Smith</lastName><age>35</age></Person>'
            . PHP_EOL . '</People>' . PHP_EOL,
            file_get_contents(self::OUTPUT_FILE)
        );
    }

    public function testWriteEmptyXmlDocument(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new XmlWriter(
            new XmlWriterStream($resource),
            new XmlWriterConfig(
                template: new XmlRootElementTemplateHandler('People')
            )
        );
        $writer->write([]);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . PHP_EOL . '<People>'
            . PHP_EOL . '</People>' . PHP_EOL,
            file_get_contents(self::OUTPUT_FILE)
        );
    }

    public function testWriteNestedXmlElement(): void
    {
        $resource = fopen(self::OUTPUT_FILE, 'w');

        $this->assertIsResource($resource);

        $writer = new XmlWriter(
            new XmlWriterStream($resource),
            new XmlWriterConfig(
                template: new XmlRootElementTemplateHandler('People')
            )
        );

        $person = new SimpleXMLElement('<Person />');
        $address = $person->addChild('address');
        $address->addChild('city', 'Amsterdam');

        $writer->write([$person]);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . PHP_EOL . '<People>'
            . PHP_EOL . '<Person><address><city>Amsterdam</city></address></Person>'
            . PHP_EOL . '</People>' . PHP_EOL,
            file_get_contents(self::OUTPUT_FILE)
        );
    }
}
