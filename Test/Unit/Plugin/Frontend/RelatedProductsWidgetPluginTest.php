<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Plugin\Frontend;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Plugin\Frontend\RelatedProductsWidgetPlugin;
use Magento\Catalog\Model\Product;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class RelatedProductsWidgetPluginTest extends TestCase
{
    public function testAppendsStackHtml(): void
    {
        $stack = $this->createMock(BadgerStack::class);
        $stack->method('setTemplate')->willReturnSelf();
        $stack->method('setProduct')->willReturnSelf();
        $stack->method('setSurface')->willReturnSelf();
        $stack->method('toHtml')->willReturn('<related/>');

        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('createBlock')->with(BadgerStack::class)->willReturn($stack);

        $subject = $this->createMock(ProductsList::class);
        $subject->method('getLayout')->willReturn($layout);

        $product = $this->createMock(Product::class);
        $result = (new RelatedProductsWidgetPlugin())
            ->afterGetProductDetailsHtml($subject, '<x/>', $product);
        self::assertSame('<x/><related/>', $result);
    }
}
