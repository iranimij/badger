<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Service\ShapeRenderer;

use Iranimij\Badger\Service\ShapeRenderer\GdShapeRenderer;
use Iranimij\Badger\Service\ShapeRenderer\ShapeSpec;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class GdShapeRendererTest extends TestCase
{
    private GdShapeRenderer $renderer;

    protected function setUp(): void
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('ext-gd not available');
        }
        $this->renderer = Bootstrap::getObjectManager()->get(GdShapeRenderer::class);
    }

    public function testRenderRoundedRect(): void
    {
        $spec = new ShapeSpec(
            kind: ShapeKind::ROUNDED_RECT,
            width: 100,
            height: 40,
            fillColor: '#ff0000',
            borderColor: '#cc0000',
            borderWidth: 1,
            cornerRadius: 6,
            text: 'SALE',
            textColor: '#ffffff',
            fontSize: 12
        );

        $png = $this->renderer->renderPng($spec);

        self::assertNotEmpty($png);
        self::assertSame("\x89PNG", substr($png, 0, 4));
    }

    public function testRenderCircle(): void
    {
        $spec = new ShapeSpec(
            kind: ShapeKind::CIRCLE,
            width: 60,
            height: 60,
            fillColor: '#00aa00',
            borderColor: '#008800',
            borderWidth: 0,
            cornerRadius: 0,
            text: 'NEW',
            textColor: '#ffffff',
            fontSize: 10
        );

        $png = $this->renderer->renderPng($spec);

        self::assertNotEmpty($png);
        self::assertSame("\x89PNG", substr($png, 0, 4));
    }

    public function testRenderRibbon(): void
    {
        $spec = new ShapeSpec(
            kind: ShapeKind::RIBBON,
            width: 120,
            height: 30,
            fillColor: '#0000ff',
            borderColor: '#0000cc',
            borderWidth: 0,
            cornerRadius: 0,
            text: 'HOT',
            textColor: '#ffffff',
            fontSize: 11
        );

        $png = $this->renderer->renderPng($spec);

        self::assertNotEmpty($png);
        self::assertSame("\x89PNG", substr($png, 0, 4));
    }

    public function testDifferentSpecsProduceDifferentImages(): void
    {
        $specA = new ShapeSpec(
            kind: ShapeKind::ROUNDED_RECT,
            width: 80, height: 30, fillColor: '#ff0000', borderColor: '#cc0000',
            borderWidth: 0, cornerRadius: 4, text: 'SALE', textColor: '#fff', fontSize: 10
        );
        $specB = new ShapeSpec(
            kind: ShapeKind::ROUNDED_RECT,
            width: 80, height: 30, fillColor: '#00ff00', borderColor: '#00cc00',
            borderWidth: 0, cornerRadius: 4, text: 'NEW', textColor: '#fff', fontSize: 10
        );

        $pngA = $this->renderer->renderPng($specA);
        $pngB = $this->renderer->renderPng($specB);

        self::assertNotSame($pngA, $pngB);
    }
}
