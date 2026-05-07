<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Enum;

use Iranimij\Badger\Model\Enum\Placement;
use PHPUnit\Framework\TestCase;

class PlacementTest extends TestCase
{
    public function testAllNineCornersExist(): void
    {
        self::assertCount(9, Placement::cases());
    }

    public function testCssClassFormat(): void
    {
        self::assertSame('iranimij-badger--top-left', Placement::TOP_LEFT->cssClass());
        self::assertSame('iranimij-badger--bottom-right', Placement::BOTTOM_RIGHT->cssClass());
        self::assertSame('iranimij-badger--middle-center', Placement::MIDDLE_CENTER->cssClass());
    }

    public function testBackingValuesStable(): void
    {
        self::assertSame(0, Placement::TOP_LEFT->value);
        self::assertSame(8, Placement::BOTTOM_RIGHT->value);
    }
}
