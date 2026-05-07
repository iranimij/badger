<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\QtyResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class QtyResolverTest extends TestCase
{
    private function ctx(float $qty): PlaceholderContext
    {
        return new PlaceholderContext($this->createMock(Product::class), null, 0, 0, $qty);
    }

    public function testIntegerQty(): void
    {
        self::assertSame('3', (new QtyResolver())->resolve('qty', $this->ctx(3.0)));
    }

    public function testFractionalQty(): void
    {
        self::assertSame('2.5', (new QtyResolver())->resolve('qty', $this->ctx(2.5)));
    }

    public function testTokenName(): void
    {
        self::assertSame('qty', (new QtyResolver())->token());
    }
}
