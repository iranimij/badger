<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\AttributeInSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class AttributeInSpecTest extends TestCase
{
    private function ctxFor(mixed $attrValue): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturn($attrValue);
        return new EvaluationContext($product, 1, 0);
    }

    public function testScalarMatchesCandidate(): void
    {
        $spec = new AttributeInSpec('color', ['red', 'blue']);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor('red')));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor('green')));
    }

    public function testCsvScalarSplit(): void
    {
        $spec = new AttributeInSpec('tags', ['new', 'hot']);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor('sale,hot,promo')));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor('old,stale')));
    }

    public function testArrayIntersection(): void
    {
        $spec = new AttributeInSpec('multi', ['1', '3']);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([2, 3, 4])));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([5, 6])));
    }

    public function testNullReturnsFalse(): void
    {
        $spec = new AttributeInSpec('color', ['red']);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(null)));
    }
}
