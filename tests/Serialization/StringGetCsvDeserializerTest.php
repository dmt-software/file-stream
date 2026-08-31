<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Reader\Csv\CsvControl;
use DMT\FileStream\Reader\Property\NamedPropertyStrategy;
use DMT\FileStream\Serialization\StringGetCsvDeserializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringGetCsvDeserializer::class)]
final class StringGetCsvDeserializerTest extends TestCase
{
    public function testDeserializesCsvRecordIntoArrayObject(): void
    {
        $deserializer = new StringGetCsvDeserializer(
            control: new CsvControl(),
            namingStrategy: new NamedPropertyStrategy(['name', 'since', 'by'])
        );

        $result = $deserializer->deserialize('PHP,1995,"Rasmus Lerdorf"');

        $this->assertSame('PHP', $result->name);
        $this->assertSame('1995', $result->since);
        $this->assertSame('Rasmus Lerdorf', $result->by);
    }

    public function testUsesConfiguredCsvControl(): void
    {
        $deserializer = new StringGetCsvDeserializer(
            control: new CsvControl(
                delimiter: ';',
                enclosure: "'"
            ),
            namingStrategy: new NamedPropertyStrategy(['name', 'since', 'by'])
        );

        $result = $deserializer->deserialize("PHP;1995;'Rasmus Lerdorf'");

        $this->assertSame('PHP', $result->name);
        $this->assertSame('1995', $result->since);
        $this->assertSame('Rasmus Lerdorf', $result->by);
    }

    public function testAppliesNamingStrategy(): void
    {
        $deserializer = new StringGetCsvDeserializer(
            control: new CsvControl(),
            namingStrategy: new NamedPropertyStrategy(['language', 'year'])
        );

        $this->assertSame(
            ['language' => 'PHP', 'year' => '1995'],
            $deserializer->deserialize('PHP,1995')->getArrayCopy()
        );
    }

    public function testSupportsEnclosedDelimiter(): void
    {
        $deserializer = new StringGetCsvDeserializer(
            control: new CsvControl(),
            namingStrategy: new NamedPropertyStrategy([
                'name',
                'description',
            ])
        );

        $result = $deserializer->deserialize(
            'PHP,"server-side, scripting language"'
        );

        $this->assertSame(
            'server-side, scripting language',
            $result->description
        );
    }
}
