<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Block\Surface;

use Iranimij\Badger\Block\Surface\TooltipBlock;
use Iranimij\Badger\Model\Config\BadgerConfigProvider;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class TooltipBlockTest extends TestCase
{
    private BadgerConfigProvider $config;
    private StoreManagerInterface $storeManager;
    private TooltipBlock $block;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventManager')->willReturn($this->createMock(ManagerInterface::class));
        $context->method('getUrlBuilder')->willReturn($this->createMock(Url::class));

        $this->config = $this->createMock(BadgerConfigProvider::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(3);
        $this->storeManager->method('getStore')->willReturn($store);

        $this->block = new TooltipBlock($context, $this->config, $this->storeManager);
    }

    public function testIsEnabledDelegatesToConfig(): void
    {
        $this->config->method('isTooltipEnabled')->with(3)->willReturn(true);
        self::assertTrue($this->block->isEnabled());
    }

    public function testReturnsConfiguredColors(): void
    {
        $this->config->method('getTooltipBackgroundColor')->willReturn('#000000');
        $this->config->method('getTooltipTextColor')->willReturn('#eeeeee');
        self::assertSame('#000000', $this->block->getDefaultBackgroundColor());
        self::assertSame('#eeeeee', $this->block->getDefaultTextColor());
    }

    public function testStoreIdFalsBackToZeroOnException(): void
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventManager')->willReturn($this->createMock(ManagerInterface::class));
        $context->method('getUrlBuilder')->willReturn($this->createMock(Url::class));

        $config = $this->createMock(BadgerConfigProvider::class);
        $config->expects(self::once())
            ->method('isTooltipEnabled')
            ->with(0)
            ->willReturn(false);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willThrowException(new \RuntimeException('no store'));

        $block = new TooltipBlock($context, $config, $storeManager);
        self::assertFalse($block->isEnabled());
    }
}
