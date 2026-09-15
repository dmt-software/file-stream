<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\NotFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(NotFilter::class)]
final class NotFilterTest extends TestCase
{
    public function testInvertAcceptedFilter(): void
    {
        $filter = new NotFilter(
            new CallbackFilter(
                static fn (): bool => true
            )
        );

        $this->assertFalse($filter->accept(new stdClass(), 0));
    }

    public function testInvertRejectedFilter(): void
    {
        $filter = new NotFilter(
            new CallbackFilter(
                static fn (): bool => false
            )
        );

        $this->assertTrue($filter->accept(new stdClass(), 0));
    }
}
