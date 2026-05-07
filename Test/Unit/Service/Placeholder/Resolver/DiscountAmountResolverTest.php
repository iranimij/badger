<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\DiscountAmountResolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use PHPUnit\Framework\TestCase;

class DiscountAmountResolverTest extends TestCase
{
    private function resolve(float $regular, ?float $final): ?string
    {
        $helper = $this->createMock(PriceHelper::class);
        $helper->method('currency')->willReturnCallback(fn (float $v) => '$' . number_format($v, 2));
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn($regular);
        $product->method('getData')->with('final_price')->willReturn($final);
        return (new DiscountAmountResolver($helper))->resolve('discount_amount', new PlaceholderContext($product));
    }

    public function testReturnsAmount(): void
    {
        self::assertSame('$3.00', $this->resolve(10.0, 7.0));
    }

    public function testNullWhenFinalGreaterOrEqualRegular(): void
    {
        self::assertNull($this->resolve(10.0, 10.0));
        self::assertNull($this->resolve(10.0, 11.0));
    }

    public function testNullWhenRegularZero(): void
    {
        self::assertNull($this->resolve(0.0, null));
    }

    public function testTokenName(): void
    {
        self::assertSame('discount_amount', (new DiscountAmountResolver($this->createMock(PriceHelper::class)))->token());
    }
}
