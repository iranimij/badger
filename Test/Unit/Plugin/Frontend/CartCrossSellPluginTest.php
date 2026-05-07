<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Plugin\Frontend;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Plugin\Frontend\CartCrossSellPlugin;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Crosssell;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class CartCrossSellPluginTest extends TestCase
{
    public function testAppendsStackHtml(): void
    {
        $stack = $this->createMock(BadgerStack::class);
        $stack->method('setTemplate')->willReturnSelf();
        $stack->method('setProduct')->willReturnSelf();
        $stack->method('setSurface')->willReturnSelf();
        $stack->method('toHtml')->willReturn('<cross/>');

        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('createBlock')->with(BadgerStack::class)->willReturn($stack);

        $subject = $this->createMock(Crosssell::class);
        $subject->method('getLayout')->willReturn($layout);

        $product = $this->createMock(Product::class);
        $result = (new CartCrossSellPlugin())->afterGetItemHtml($subject, '<item/>', $product);
        self::assertSame('<item/><cross/>', $result);
    }

    public function testReturnsOriginalWhenNoProduct(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects(self::never())->method('createBlock');

        $subject = $this->createMock(Crosssell::class);
        $subject->method('getLayout')->willReturn($layout);

        $result = (new CartCrossSellPlugin())->afterGetItemHtml($subject, '<item/>', null);
        self::assertSame('<item/>', $result);
    }
}
