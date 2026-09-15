<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader;

use ArrayObject;
use DMT\FileStream\Config\CsvReaderConfig;
use DMT\FileStream\Reader\CsvReader;
use DMT\FileStream\Record\Mapping\Csv\PredefinedPropertyMapper;
use DMT\FileStream\Stream\ResourceReaderStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvReader::class)]
#[Group('integration')]
final class CsvReaderTest extends TestCase
{
    public function testReadCsvRecords(): void
    {
        $resource = fopen(__DIR__ . '/../fixtures/csv/people.csv', 'r');

        $reader = new CsvReader(
            new ResourceReaderStream($resource),
            new CsvReaderConfig(
                propertyMapper: new PredefinedPropertyMapper([
                    'firstName',
                    'lastName',
                    'age',
                ])
            )
        );

        $results = iterator_to_array($reader->getResults());

        $this->assertCount(3, $results);
        $this->assertContainsOnlyInstancesOf(ArrayObject::class, $results);

        $this->assertSame(
            [
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'age' => 'age',
            ],
            $results[0]->getArrayCopy()
        );

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => '42',
            ],
            $results[1]->getArrayCopy()
        );

        $this->assertSame(
            [
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'age' => '35',
            ],
            $results[2]->getArrayCopy()
        );
    }
}
