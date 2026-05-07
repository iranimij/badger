<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\AttributeRangeSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class AttributeRangeSpecTest extends TestCase
{
    private function ctxFor(mixed $attrValue): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturn($attrValue);
        return new EvaluationContext($product, 1, 0);
    }

    public function testInsideRange(): void
    {
        $spec = new AttributeRangeSpec('weight', 1.0, 10.0);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor('5')));
    }

    public function testBoundariesInclusive(): void
    {
        $spec = new AttributeRangeSpec('weight', 1.0, 10.0);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor(1.0)));
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor(10.0)));
    }

    public function testOutsideRange(): void
    {
        $spec = new AttributeRangeSpec('weight', 1.0, 10.0);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(0.5)));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(11)));
    }

    public function testNullBoundsOpenOneSide(): void
    {
        $spec = new AttributeRangeSpec('weight', null, 10.0);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor(-100)));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(11)));
    }

    public function testNonNumericReturnsFalse(): void
    {
        $spec = new AttributeRangeSpec('weight', 0.0, 10.0);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor('heavy')));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(null)));
    }
}
