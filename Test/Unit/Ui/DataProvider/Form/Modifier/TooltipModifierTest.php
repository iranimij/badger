<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Ui\DataProvider\Form\Modifier\TooltipModifier;
use PHPUnit\Framework\TestCase;

class TooltipModifierTest extends TestCase
{
    public function testFillsDefaultsWhenMissing(): void
    {
        $modifier = new TooltipModifier();
        $data = $modifier->modifyData([1 => ['name' => 'x']]);
        self::assertArrayHasKey('tooltip', $data[1]);
        self::assertSame(false, $data[1]['tooltip'][TooltipInterface::IS_ENABLED]);
        self::assertNull($data[1]['tooltip'][TooltipInterface::BODY_TEXT]);
    }

    public function testNormalizesEnabledFlag(): void
    {
        $modifier = new TooltipModifier();
        $data = $modifier->modifyData([
            1 => ['tooltip' => [TooltipInterface::IS_ENABLED => true, TooltipInterface::BODY_TEXT => 'hi']],
        ]);
        self::assertSame('1', $data[1]['tooltip'][TooltipInterface::IS_ENABLED]);
        self::assertSame('hi', $data[1]['tooltip'][TooltipInterface::BODY_TEXT]);
    }

    public function testNormalizesFalsyEnabledFlag(): void
    {
        $modifier = new TooltipModifier();
        $data = $modifier->modifyData([
            1 => ['tooltip' => [TooltipInterface::IS_ENABLED => 0]],
        ]);
        self::assertSame('0', $data[1]['tooltip'][TooltipInterface::IS_ENABLED]);
    }

    public function testMetaPassthrough(): void
    {
        $modifier = new TooltipModifier();
        self::assertSame(['x' => 1], $modifier->modifyMeta(['x' => 1]));
    }

    public function testSkipsNonArrayRows(): void
    {
        $modifier = new TooltipModifier();
        $data = $modifier->modifyData([1 => 'scalar']);
        self::assertSame('scalar', $data[1]);
    }
}
