<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Resolution;

use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\ResolvedBadger;
use Iranimij\Badger\Model\Resolution\ExclusivityPolicy;
use PHPUnit\Framework\TestCase;

class ExclusivityPolicyTest extends TestCase
{
    public function testExclusiveSuppressesLowerAtSamePlacement(): void
    {
        $top = $this->make(1, true, Placement::TOP_LEFT);
        $tail = $this->make(2, false, Placement::TOP_LEFT);
        $kept = (new ExclusivityPolicy())->apply([$top, $tail]);
        self::assertCount(1, $kept);
        self::assertSame(1, $kept[0]->badgerId);
    }

    public function testExclusiveDoesNotSuppressDifferentPlacement(): void
    {
        $top = $this->make(1, true, Placement::TOP_LEFT);
        $other = $this->make(2, false, Placement::BOTTOM_RIGHT);
        $kept = (new ExclusivityPolicy())->apply([$top, $other]);
        self::assertCount(2, $kept);
    }

    public function testExclusiveDoesNotSuppressDifferentSurface(): void
    {
        $top = $this->make(1, true, Placement::TOP_LEFT, Surface::CATEGORY_GRID);
        $other = $this->make(2, false, Placement::TOP_LEFT, Surface::PRODUCT_PAGE);
        $kept = (new ExclusivityPolicy())->apply([$top, $other]);
        self::assertCount(2, $kept);
    }

    public function testNonExclusiveKeepsAll(): void
    {
        $a = $this->make(1, false, Placement::TOP_LEFT);
        $b = $this->make(2, false, Placement::TOP_LEFT);
        $kept = (new ExclusivityPolicy())->apply([$a, $b]);
        self::assertCount(2, $kept);
    }

    private function make(int $id, bool $exclusive, Placement $placement, Surface $surface = Surface::CATEGORY_GRID): ResolvedBadger
    {
        return new ResolvedBadger(
            $id,
            'b' . $id,
            0,
            $exclusive,
            $surface,
            $placement,
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
