<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Enum;

use Iranimij\Badger\Model\Enum\Surface;
use PHPUnit\Framework\TestCase;

class SurfaceTest extends TestCase
{
    public function testAllFiveSurfacesExist(): void
    {
        self::assertCount(5, Surface::cases());
    }

    public function testCodeIsLowercaseOfName(): void
    {
        self::assertSame('category_grid', Surface::CATEGORY_GRID->code());
        self::assertSame('product_page', Surface::PRODUCT_PAGE->code());
        self::assertSame('cart_crosssell', Surface::CART_CROSSSELL->code());
    }
}
