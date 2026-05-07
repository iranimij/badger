<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Block\Surface;

use Iranimij\Badger\Block\Surface\TooltipBlock;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class TooltipBlockTest extends TestCase
{
    private TooltipBlock $block;

    protected function setUp(): void
    {
        $this->block = Bootstrap::getObjectManager()->get(TooltipBlock::class);
    }

    public function testIsEnabledReturnsBool(): void
    {
        $this->assertIsBool($this->block->isEnabled());
    }

    public function testGetDefaultBackgroundColorReturnsNonEmptyString(): void
    {
        $color = $this->block->getDefaultBackgroundColor();
        $this->assertIsString($color);
        $this->assertNotEmpty($color);
    }

    public function testGetDefaultTextColorReturnsNonEmptyString(): void
    {
        $color = $this->block->getDefaultTextColor();
        $this->assertIsString($color);
        $this->assertNotEmpty($color);
    }

    /**
     * @magentoConfigFixture default/iranimij_badger/tooltip/enabled 1
     * @magentoConfigFixture default/iranimij_badger/tooltip/background_color #111111
     * @magentoConfigFixture default/iranimij_badger/tooltip/text_color #eeeeee
     */
    public function testReadsTooltipConfigValues(): void
    {
        $this->assertTrue($this->block->isEnabled());
        $this->assertSame('#111111', $this->block->getDefaultBackgroundColor());
        $this->assertSame('#eeeeee', $this->block->getDefaultTextColor());
    }

    /**
     * @magentoConfigFixture default/iranimij_badger/tooltip/enabled 0
     */
    public function testIsEnabledFalseWhenConfigDisabled(): void
    {
        $this->assertFalse($this->block->isEnabled());
    }
}
