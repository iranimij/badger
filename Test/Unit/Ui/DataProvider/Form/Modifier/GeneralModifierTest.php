<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Ui\DataProvider\Form\Modifier\GeneralModifier;
use PHPUnit\Framework\TestCase;

class GeneralModifierTest extends TestCase
{
    public function testNormalizesBooleansToStringFlags(): void
    {
        $modifier = new GeneralModifier();
        $result = $modifier->modifyData([
            1 => ['is_enabled' => true, 'is_exclusive' => false, 'use_for_parent' => 1],
        ]);
        self::assertSame('1', $result[1]['is_enabled']);
        self::assertSame('0', $result[1]['is_exclusive']);
        self::assertSame('1', $result[1]['use_for_parent']);
    }

    public function testHandlesMissingKeys(): void
    {
        $modifier = new GeneralModifier();
        $result = $modifier->modifyData([5 => ['name' => 'x']]);
        self::assertSame('0', $result[5]['is_enabled']);
        self::assertSame('0', $result[5]['is_exclusive']);
    }

    public function testMetaPassthrough(): void
    {
        $modifier = new GeneralModifier();
        self::assertSame(['foo' => 'bar'], $modifier->modifyMeta(['foo' => 'bar']));
    }

    public function testSkipsNonArrayRows(): void
    {
        $modifier = new GeneralModifier();
        $result = $modifier->modifyData([1 => 'scalar']);
        self::assertSame('scalar', $result[1]);
    }
}
