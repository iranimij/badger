<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\OnSaleSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class OnSaleSpecTest extends TestCase
{
    /** @param array<string, mixed> $data */
    private function ctxFor(array $data): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturnCallback(fn (string $k) => $data[$k] ?? null);
        return new EvaluationContext($product, 1, 0);
    }

    public function testActiveSpecialPriceQualifies(): void
    {
        $spec = new OnSaleSpec();
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([
            'price' => 100.0,
            'special_price' => 80.0,
        ])));
    }

    public function testNoSpecialPrice(): void
    {
        $spec = new OnSaleSpec();
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(['price' => 100.0])));
    }

    public function testSpecialEqualOrAboveRegular(): void
    {
        $spec = new OnSaleSpec();
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([
            'price' => 100.0,
            'special_price' => 100.0,
        ])));
    }

    public function testSpecialFromDateInFuture(): void
    {
        $spec = new OnSaleSpec();
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([
            'price' => 100.0,
            'special_price' => 80.0,
            'special_from_date' => date('Y-m-d H:i:s', time() + 86400),
        ])));
    }

    public function testSpecialToDateInPast(): void
    {
        $spec = new OnSaleSpec();
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([
            'price' => 100.0,
            'special_price' => 80.0,
            'special_to_date' => date('Y-m-d H:i:s', time() - 86400),
        ])));
    }
}
