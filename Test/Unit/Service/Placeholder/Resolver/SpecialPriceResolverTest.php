<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\SpecialPriceResolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use PHPUnit\Framework\TestCase;

class SpecialPriceResolverTest extends TestCase
{
    public function testReturnsFormattedSpecialPrice(): void
    {
        $helper = $this->createMock(PriceHelper::class);
        $helper->method('currency')->willReturnCallback(fn (float $v) => '$' . number_format($v, 2));
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('special_price')->willReturn(5.5);

        self::assertSame('$5.50', (new SpecialPriceResolver($helper))->resolve('special_price', new PlaceholderContext($product)));
    }

    public function testReturnsNullWhenUnset(): void
    {
        $helper = $this->createMock(PriceHelper::class);
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('special_price')->willReturn(null);
        self::assertNull((new SpecialPriceResolver($helper))->resolve('special_price', new PlaceholderContext($product)));
    }

    public function testTokenName(): void
    {
        self::assertSame('special_price', (new SpecialPriceResolver($this->createMock(PriceHelper::class)))->token());
    }
}
