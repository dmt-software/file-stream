<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader;

use DMT\FileStream\Config\JsonReaderConfig;
use DMT\FileStream\Reader\JsonReader;
use DMT\FileStream\Stream\JsonReaderStream;
use DMT\FileStream\Structured\Path\DotSeparatedPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JsonReader::class)]
#[Group('integration')]
final class JsonReaderTest extends TestCase
{
    public function testReadJsonObjects(): void
    {
        $resource = fopen(__DIR__ . '/../fixtures/json/people.json', 'r');

        $reader = new JsonReader(
            new JsonReaderStream($resource),
            new JsonReaderConfig(
                path: new DotSeparatedPath('.people')
            )
        );

        $results = iterator_to_array($reader->getResults());

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(stdClass::class, $results);

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => 42,
            ],
            get_object_vars($results[0])
        );

        $this->assertSame(
            [
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'age' => 35,
            ],
            get_object_vars($results[1])
        );
    }
}
