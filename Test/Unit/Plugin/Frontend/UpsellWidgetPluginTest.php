<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Plugin\Frontend;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Plugin\Frontend\UpsellWidgetPlugin;
use Magento\Catalog\Block\Product\ProductList\Upsell;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class UpsellWidgetPluginTest extends TestCase
{
    public function testAppendsStackHtml(): void
    {
        $stack = $this->createMock(BadgerStack::class);
        $stack->method('setTemplate')->willReturnSelf();
        $stack->method('setProduct')->willReturnSelf();
        $stack->method('setSurface')->willReturnSelf();
        $stack->method('toHtml')->willReturn('<up/>');

        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('createBlock')->with(BadgerStack::class)->willReturn($stack);

        $subject = $this->createMock(Upsell::class);
        $subject->method('getLayout')->willReturn($layout);

        $product = $this->createMock(Product::class);
        $result = (new UpsellWidgetPlugin())->afterGetItemHtml($subject, '<item/>', $product);
        self::assertSame('<item/><up/>', $result);
    }

    public function testReturnsOriginalWhenNoProduct(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects(self::never())->method('createBlock');

        $subject = $this->createMock(Upsell::class);
        $subject->method('getLayout')->willReturn($layout);

        $result = (new UpsellWidgetPlugin())->afterGetItemHtml($subject, '<item/>', null);
        self::assertSame('<item/>', $result);
    }
}
