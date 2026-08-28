<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Stream;

use DMT\FileStream\Reader\Csv\CsvControl;
use DMT\FileStream\Reader\Csv\CsvLineParser;
use DMT\FileStream\Reader\Stream\CsvLineIterator;
use PHPUnit\Framework\TestCase;

final class CsvLineIteratorTest extends TestCase
{
    public function testIteratesCsvRecords(): void
    {
        $iterator = $this->iterator();

        $this->assertSame(
            [
                'name,since,by',
                'Javascript,1995,"Brendan Eich"',
                '',
                'PHP,1995,"Rasmus Lerdorf"',
            ],
            array_values(iterator_to_array($iterator))
        );
    }

    public function testUsesSequentialKeys(): void
    {
        $iterator = $this->iterator();

        $this->assertSame(
            [0, 1, 2, 3],
            array_keys(iterator_to_array($iterator))
        );
    }

    public function testRewindPositionsIteratorOnFirstRecord(): void
    {
        $iterator = $this->iterator();

        $iterator->rewind();

        $this->assertTrue($iterator->valid());
        $this->assertSame(0, $iterator->key());
        $this->assertSame('name,since,by', $iterator->current());
    }

    public function testNextAdvancesToFollowingRecord(): void
    {
        $iterator = $this->iterator();

        $iterator->rewind();
        $iterator->next();

        $this->assertTrue($iterator->valid());
        $this->assertSame(1, $iterator->key());
        $this->assertSame(
            'Javascript,1995,"Brendan Eich"',
            $iterator->current()
        );
    }

    public function testIteratorBecomesInvalidAtEndOfStream(): void
    {
        $iterator = $this->iterator();

        iterator_to_array($iterator);

        $this->assertFalse($iterator->valid());
    }

    public function testIteratorCannotRestartAfterItHasStarted(): void
    {
        $iterator = $this->iterator();

        $this->assertCount(4, iterator_to_array($iterator));
        $this->assertSame([], iterator_to_array($iterator));
    }

    private function iterator(): CsvLineIterator
    {
        $stream = fopen(dirname(__DIR__, 2) . '/fixtures/csv/iterator.csv', 'r');

        $this->assertIsResource($stream);

        return new CsvLineIterator(
            new CsvLineParser(
                stream: $stream,
                control: new CsvControl()
            )
        );
    }
}
