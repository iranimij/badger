<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\PriceRangeSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class PriceRangeSpecTest extends TestCase
{
    /** @param array<string, mixed> $data */
    private function ctxFor(array $data): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturnCallback(fn (string $k) => $data[$k] ?? null);
        return new EvaluationContext($product, 1, 0);
    }

    public function testFinalPricePrefersSpecial(): void
    {
        $spec = new PriceRangeSpec(0.0, 50.0, PriceRangeSpec::FIELD_FINAL);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([
            'price' => 100.0,
            'special_price' => 40.0,
        ])));
    }

    public function testFinalPriceFallsBackToRegular(): void
    {
        $spec = new PriceRangeSpec(0.0, 50.0, PriceRangeSpec::FIELD_FINAL);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(['price' => 100.0])));
    }

    public function testRegularIgnoresSpecial(): void
    {
        $spec = new PriceRangeSpec(50.0, 200.0, PriceRangeSpec::FIELD_REGULAR);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([
            'price' => 100.0,
            'special_price' => 10.0,
        ])));
    }

    public function testNonNumericFalse(): void
    {
        $spec = new PriceRangeSpec(null, null);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(['price' => 'free'])));
    }

    public function testUnknownFieldRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PriceRangeSpec(null, null, 'cost');
    }
}
