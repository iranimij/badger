<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Plugin\Frontend;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Plugin\Frontend\ProductViewAfterHtmlPlugin;
use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class ProductViewAfterHtmlPluginTest extends TestCase
{
    public function testAppendsStackHtml(): void
    {
        $product = $this->createMock(Product::class);

        $stack = $this->createMock(BadgerStack::class);
        $stack->method('setTemplate')->willReturnSelf();
        $stack->method('setProduct')->willReturnSelf();
        $stack->method('setSurface')->willReturnSelf();
        $stack->method('toHtml')->willReturn('<pdp/>');

        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('createBlock')->with(BadgerStack::class)->willReturn($stack);

        $subject = $this->createMock(Gallery::class);
        $subject->method('getLayout')->willReturn($layout);
        $subject->method('getProduct')->willReturn($product);

        $result = (new ProductViewAfterHtmlPlugin())->afterToHtml($subject, '<gallery/>');
        self::assertSame('<gallery/><pdp/>', $result);
    }

    public function testReturnsOriginalWhenNoProduct(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects(self::never())->method('createBlock');

        $subject = $this->createMock(Gallery::class);
        $subject->method('getLayout')->willReturn($layout);
        $subject->method('getProduct')->willReturn(null);

        $result = (new ProductViewAfterHtmlPlugin())->afterToHtml($subject, '<gallery/>');
        self::assertSame('<gallery/>', $result);
    }
}
