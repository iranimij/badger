<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\AttributeEqualsSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class AttributeEqualsSpecTest extends TestCase
{
    private function ctxFor(mixed $attrValue, string $code = 'color'): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->with($code)->willReturn($attrValue);
        return new EvaluationContext($product, 1, 0);
    }

    public function testScalarMatchCoercesToString(): void
    {
        $spec = new AttributeEqualsSpec('color', '42');
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor(42)));
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor('42')));
    }

    public function testReturnsFalseOnMismatch(): void
    {
        $spec = new AttributeEqualsSpec('color', 'red');
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor('blue')));
    }

    public function testReturnsFalseWhenValueNull(): void
    {
        $spec = new AttributeEqualsSpec('color', 'red');
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(null)));
    }
}
