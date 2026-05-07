<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Resolution;

use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\ResolvedBadger;
use Iranimij\Badger\Model\Resolution\ExclusivityPolicy;
use Iranimij\Badger\Model\Resolution\PriorityResolver;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class PriorityResolverTest extends TestCase
{
    private PriorityResolver $resolver;
    private ExclusivityPolicy $exclusivity;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->resolver = $om->get(PriorityResolver::class);
        $this->exclusivity = $om->get(ExclusivityPolicy::class);
    }

    public function testHigherPriorityBadgerComesFirst(): void
    {
        $low = $this->makeResolved(1, 5, false);
        $high = $this->makeResolved(2, 100, false);
        $mid = $this->makeResolved(3, 50, false);

        $sorted = $this->resolver->sort([$low, $high, $mid]);

        self::assertSame(100, $sorted[0]->priority);
        self::assertSame(50, $sorted[1]->priority);
        self::assertSame(5, $sorted[2]->priority);
    }

    public function testExclusiveBadgerBlocksSameSurfacePlacement(): void
    {
        $exclusive = $this->makeResolved(1, 100, true, Surface::CATEGORY_GRID, Placement::TOP_LEFT);
        $normal1 = $this->makeResolved(2, 50, false, Surface::CATEGORY_GRID, Placement::TOP_LEFT);
        $normal2 = $this->makeResolved(3, 10, false, Surface::CATEGORY_GRID, Placement::TOP_LEFT);

        $filtered = $this->exclusivity->apply([$exclusive, $normal1, $normal2]);

        self::assertCount(1, $filtered);
        self::assertSame(1, $filtered[0]->badgerId);
    }

    public function testExclusiveBadgerDoesNotBlockDifferentPlacement(): void
    {
        $exclusive = $this->makeResolved(1, 100, true, Surface::CATEGORY_GRID, Placement::TOP_LEFT);
        $other = $this->makeResolved(2, 50, false, Surface::CATEGORY_GRID, Placement::TOP_RIGHT);

        $filtered = $this->exclusivity->apply([$exclusive, $other]);

        self::assertCount(2, $filtered);
    }

    public function testNonExclusiveBadgesAllAllowed(): void
    {
        $b1 = $this->makeResolved(1, 100, false);
        $b2 = $this->makeResolved(2, 50, false);
        $b3 = $this->makeResolved(3, 10, false);

        $filtered = $this->exclusivity->apply([$b1, $b2, $b3]);

        self::assertCount(3, $filtered);
    }

    public function testEmptyListReturnsEmpty(): void
    {
        self::assertSame([], $this->resolver->sort([]));
        self::assertSame([], $this->exclusivity->apply([]));
    }

    public function testEqualPriorityOrderedByBadgerId(): void
    {
        $b3 = $this->makeResolved(3, 50, false);
        $b1 = $this->makeResolved(1, 50, false);
        $b2 = $this->makeResolved(2, 50, false);

        $sorted = $this->resolver->sort([$b3, $b1, $b2]);

        self::assertSame(1, $sorted[0]->badgerId);
        self::assertSame(2, $sorted[1]->badgerId);
        self::assertSame(3, $sorted[2]->badgerId);
    }

    private function makeResolved(
        int $badgerId,
        int $priority,
        bool $exclusive,
        Surface $surface = Surface::CATEGORY_GRID,
        Placement $placement = Placement::TOP_LEFT
    ): ResolvedBadger {
        return new ResolvedBadger(
            badgerId: $badgerId,
            name: "Badge {$badgerId}",
            priority: $priority,
            isExclusive: $exclusive,
            surface: $surface,
            placement: $placement,
            shape: ShapeKind::ROUNDED_RECT,
            labelText: '',
            imagePath: null,
            redirectUrl: null,
            altText: null,
            cssClass: null,
            sizePercent: 25,
            tooltipText: null,
            tooltipBgColor: null,
            tooltipTextColor: null
        );
    }
}
