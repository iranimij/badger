<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Enum;

use Iranimij\Badger\Model\Enum\ShapeKind;
use PHPUnit\Framework\TestCase;

class ShapeKindTest extends TestCase
{
    public function testTextDoesNotRenderImage(): void
    {
        self::assertFalse(ShapeKind::TEXT->rendersImage());
    }

    public function testNonTextShapesRenderImage(): void
    {
        self::assertTrue(ShapeKind::IMAGE->rendersImage());
        self::assertTrue(ShapeKind::ROUNDED_RECT->rendersImage());
        self::assertTrue(ShapeKind::CIRCLE->rendersImage());
        self::assertTrue(ShapeKind::RIBBON->rendersImage());
    }
}
