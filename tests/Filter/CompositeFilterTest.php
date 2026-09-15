<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\CompositeFilter;
use DMT\FileStream\Filter\Operation\CompositeMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(CompositeFilter::class)]
final class CompositeFilterTest extends TestCase
{
    public function testAcceptWhenAllFiltersAccept(): void
    {
        $filter = new CompositeFilter([
            new CallbackFilter(static fn (): bool => true),
            new CallbackFilter(static fn (): bool => true),
        ]);

        $this->assertTrue($filter->accept(new stdClass(), 0));
    }

    public function testRejectWhenOneFilterRejectsInAllMode(): void
    {
        $filter = new CompositeFilter([
            new CallbackFilter(static fn (): bool => true),
            new CallbackFilter(static fn (): bool => false),
        ]);

        $this->assertFalse($filter->accept(new stdClass(), 0));
    }

    public function testAcceptWhenAnyFilterAccepts(): void
    {
        $filter = new CompositeFilter(
            [
                new CallbackFilter(static fn (): bool => false),
                new CallbackFilter(static fn (): bool => true),
            ],
            CompositeMode::Any
        );

        $this->assertTrue($filter->accept(new stdClass(), 0));
    }

    public function testRejectsWhenAllFiltersRejectInAnyMode(): void
    {
        $filter = new CompositeFilter(
            [
                new CallbackFilter(static fn (): bool => false),
                new CallbackFilter(static fn (): bool => false),
            ],
            CompositeMode::Any
        );

        $this->assertFalse($filter->accept(new stdClass(), 0));
    }

    public function testAcceptWhenNoFilterAccepts(): void
    {
        $filter = new CompositeFilter(
            [
                new CallbackFilter(static fn (): bool => false),
                new CallbackFilter(static fn (): bool => false),
            ],
            CompositeMode::None
        );

        $this->assertTrue($filter->accept(new stdClass(), 0));
    }

    public function testRejectWhenAnyFilterAcceptsInNoneMode(): void
    {
        $filter = new CompositeFilter(
            [
                new CallbackFilter(static fn (): bool => false),
                new CallbackFilter(static fn (): bool => true),
            ],
            CompositeMode::None
        );

        $this->assertFalse($filter->accept(new stdClass(), 0));
    }
}
