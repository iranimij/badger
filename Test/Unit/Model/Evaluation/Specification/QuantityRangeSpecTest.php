<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\QuantityRangeSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class QuantityRangeSpecTest extends TestCase
{
    private function ctxQty(float $qty): EvaluationContext
    {
        return new EvaluationContext($this->createMock(Product::class), 1, 0, $qty);
    }

    public function testInsideRange(): void
    {
        $spec = new QuantityRangeSpec(1.0, 10.0);
        self::assertTrue($spec->isSatisfiedBy($this->ctxQty(5.0)));
    }

    public function testBelowMin(): void
    {
        $spec = new QuantityRangeSpec(2.0, null);
        self::assertFalse($spec->isSatisfiedBy($this->ctxQty(1.0)));
    }

    public function testAboveMax(): void
    {
        $spec = new QuantityRangeSpec(null, 5.0);
        self::assertFalse($spec->isSatisfiedBy($this->ctxQty(5.5)));
    }

    public function testUnbounded(): void
    {
        $spec = new QuantityRangeSpec(null, null);
        self::assertTrue($spec->isSatisfiedBy($this->ctxQty(0.0)));
        self::assertTrue($spec->isSatisfiedBy($this->ctxQty(1000.0)));
    }
}
