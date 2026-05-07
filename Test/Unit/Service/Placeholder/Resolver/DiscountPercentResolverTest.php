<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\DiscountPercentResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class DiscountPercentResolverTest extends TestCase
{
    private function resolvePercent(float $regular, ?float $final): ?string
    {
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn($regular);
        $product->method('getData')->with('final_price')->willReturn($final);
        return (new DiscountPercentResolver())->resolve('discount_percent', new PlaceholderContext($product));
    }

    public function testReturnsPercent(): void
    {
        self::assertSame('30%', $this->resolvePercent(10.0, 7.0));
    }

    public function testRounds(): void
    {
        self::assertSame('33%', $this->resolvePercent(10.0, 6.67));
    }

    public function testNullWhenNoDiscount(): void
    {
        self::assertNull($this->resolvePercent(10.0, 10.0));
    }

    public function testTokenName(): void
    {
        self::assertSame('discount_percent', (new DiscountPercentResolver())->token());
    }
}
