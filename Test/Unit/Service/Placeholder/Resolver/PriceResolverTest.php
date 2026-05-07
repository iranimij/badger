<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\PriceResolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use PHPUnit\Framework\TestCase;

class PriceResolverTest extends TestCase
{
    public function testUsesFinalPriceWhenAvailable(): void
    {
        $helper = $this->createMock(PriceHelper::class);
        $helper->method('currency')->willReturnCallback(fn (float $v) => '$' . number_format($v, 2));
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('final_price')->willReturn(7.5);

        $resolver = new PriceResolver($helper);
        $ctx = new PlaceholderContext($product);
        self::assertSame('$7.50', $resolver->resolve('price', $ctx));
    }

    public function testFallsBackToRegularPrice(): void
    {
        $helper = $this->createMock(PriceHelper::class);
        $helper->method('currency')->willReturnCallback(fn (float $v) => '$' . number_format($v, 2));
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('final_price')->willReturn(null);
        $product->method('getPrice')->willReturn(12.0);

        $resolver = new PriceResolver($helper);
        $ctx = new PlaceholderContext($product);
        self::assertSame('$12.00', $resolver->resolve('price', $ctx));
    }

    public function testTokenName(): void
    {
        self::assertSame('price', (new PriceResolver($this->createMock(PriceHelper::class)))->token());
    }
}
