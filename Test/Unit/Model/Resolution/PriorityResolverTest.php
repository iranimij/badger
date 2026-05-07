<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Resolution;

use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\ResolvedBadger;
use Iranimij\Badger\Model\Resolution\PriorityResolver;
use PHPUnit\Framework\TestCase;

class PriorityResolverTest extends TestCase
{
    public function testHighestPriorityFirst(): void
    {
        $low = $this->make(1, 1);
        $high = $this->make(2, 9);
        $sorted = (new PriorityResolver())->sort([$low, $high]);
        self::assertSame(2, $sorted[0]->badgerId);
        self::assertSame(1, $sorted[1]->badgerId);
    }

    public function testStableByIdOnTie(): void
    {
        $a = $this->make(7, 5);
        $b = $this->make(3, 5);
        $sorted = (new PriorityResolver())->sort([$a, $b]);
        self::assertSame(3, $sorted[0]->badgerId);
        self::assertSame(7, $sorted[1]->badgerId);
    }

    public function testEmptyInputReturnsEmpty(): void
    {
        self::assertSame([], (new PriorityResolver())->sort([]));
    }

    private function make(int $id, int $priority): ResolvedBadger
    {
        return new ResolvedBadger(
            $id,
            'b' . $id,
            $priority,
            false,
            Surface::CATEGORY_GRID,
            Placement::TOP_LEFT,
            ShapeKind::ROUNDED_RECT,
            'L',
            null,
            null,
            null,
            null,
            25,
            null,
            null,
            null
        );
    }
}
