<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\ShapeRenderer;

use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Service\ShapeRenderer\GdShapeRenderer;
use Iranimij\Badger\Service\ShapeRenderer\ShapeSpec;
use PHPUnit\Framework\TestCase;

class GdShapeRendererTest extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension not available.');
        }
    }

    private function makeSpec(ShapeKind $kind): ShapeSpec
    {
        return new ShapeSpec($kind, 80, 40, '#ff3300', '#000000', 1, 6, 'OK', '#ffffff', 18);
    }

    public function testRendersValidPngForRoundedRect(): void
    {
        $bytes = (new GdShapeRenderer())->renderPng($this->makeSpec(ShapeKind::ROUNDED_RECT));
        self::assertNotEmpty($bytes);
        self::assertSame("\x89PNG", substr($bytes, 0, 4));
    }

    public function testRendersValidPngForCircle(): void
    {
        $bytes = (new GdShapeRenderer())->renderPng($this->makeSpec(ShapeKind::CIRCLE));
        self::assertSame("\x89PNG", substr($bytes, 0, 4));
    }

    public function testRendersValidPngForRibbon(): void
    {
        $bytes = (new GdShapeRenderer())->renderPng($this->makeSpec(ShapeKind::RIBBON));
        self::assertSame("\x89PNG", substr($bytes, 0, 4));
    }

    public function testHandlesEmptyText(): void
    {
        $spec = new ShapeSpec(ShapeKind::ROUNDED_RECT, 40, 20, '#ffffff', '#000000', 0, 4, '', '#000000', 10);
        $bytes = (new GdShapeRenderer())->renderPng($spec);
        self::assertSame("\x89PNG", substr($bytes, 0, 4));
    }
}
