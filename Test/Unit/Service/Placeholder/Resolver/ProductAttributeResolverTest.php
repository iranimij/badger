<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\ProductAttributeResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class ProductAttributeResolverTest extends TestCase
{
    public function testResolvesAttrColon(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('color')->willReturn('red');
        $resolver = new ProductAttributeResolver();
        self::assertSame('red', $resolver->resolve('attr:color', new PlaceholderContext($product)));
    }

    public function testNullWhenTokenLacksColon(): void
    {
        $product = $this->createMock(Product::class);
        self::assertNull((new ProductAttributeResolver())->resolve('attr', new PlaceholderContext($product)));
    }

    public function testNullWhenAttributeEmpty(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturn('');
        self::assertNull((new ProductAttributeResolver())->resolve('attr:missing', new PlaceholderContext($product)));
    }

    public function testNullWhenAttributeArray(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturn(['a', 'b']);
        self::assertNull((new ProductAttributeResolver())->resolve('attr:tags', new PlaceholderContext($product)));
    }

    public function testTokenName(): void
    {
        self::assertSame('attr:*', (new ProductAttributeResolver())->token());
    }
}
