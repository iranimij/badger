<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\ShapeRenderer;

use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Service\ShapeRenderer\ShapeSpec;
use PHPUnit\Framework\TestCase;

class ShapeSpecTest extends TestCase
{
    private function spec(array $overrides = []): ShapeSpec
    {
        $defaults = [
            'kind' => ShapeKind::ROUNDED_RECT,
            'width' => 120,
            'height' => 60,
            'fillColor' => '#ff0000',
            'borderColor' => '#000000',
            'borderWidth' => 2,
            'cornerRadius' => 10,
            'text' => 'SALE',
            'textColor' => '#ffffff',
            'fontSize' => 24,
        ];
        $args = array_replace($defaults, $overrides);
        return new ShapeSpec(
            $args['kind'],
            $args['width'],
            $args['height'],
            $args['fillColor'],
            $args['borderColor'],
            $args['borderWidth'],
            $args['cornerRadius'],
            $args['text'],
            $args['textColor'],
            $args['fontSize']
        );
    }

    public function testHashIsStableForSameInputs(): void
    {
        self::assertSame($this->spec()->hash(), $this->spec()->hash());
    }

    public function testHashChangesOnDifferentInput(): void
    {
        self::assertNotSame(
            $this->spec()->hash(),
            $this->spec(['text' => 'NEW'])->hash()
        );
    }

    public function testHashIsHexAndFixedLength(): void
    {
        $hash = $this->spec()->hash();
        self::assertSame(32, strlen($hash));
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $hash);
    }

    public function testPropertiesReadonly(): void
    {
        $spec = $this->spec();
        self::assertSame(ShapeKind::ROUNDED_RECT, $spec->kind);
        self::assertSame(120, $spec->width);
        self::assertSame('SALE', $spec->text);
    }
}
