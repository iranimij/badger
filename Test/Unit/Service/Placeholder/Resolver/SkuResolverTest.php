<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\SkuResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class SkuResolverTest extends TestCase
{
    public function testReturnsSku(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getSku')->willReturn('ABC-1');
        self::assertSame('ABC-1', (new SkuResolver())->resolve('sku', new PlaceholderContext($product)));
    }

    public function testNullOnEmpty(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getSku')->willReturn('');
        self::assertNull((new SkuResolver())->resolve('sku', new PlaceholderContext($product)));
    }

    public function testTokenName(): void
    {
        self::assertSame('sku', (new SkuResolver())->token());
    }
}
