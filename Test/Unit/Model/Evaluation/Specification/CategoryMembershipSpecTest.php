<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\CategoryMembershipSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class CategoryMembershipSpecTest extends TestCase
{
    /** @param list<int>|null $productCats */
    private function ctxFor(?array $productCats): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturnCallback(
            static fn (string $k) => $k === 'category_ids' ? $productCats : null
        );
        return new EvaluationContext($product, 1, 0);
    }

    public function testAnyMatch(): void
    {
        $spec = new CategoryMembershipSpec([10, 20, 30]);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([5, 20, 99])));
    }

    public function testAnyNoMatch(): void
    {
        $spec = new CategoryMembershipSpec([10, 20]);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([1, 2, 3])));
    }

    public function testAllMode(): void
    {
        $spec = new CategoryMembershipSpec([10, 20], CategoryMembershipSpec::MODE_ALL);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([10, 20, 30])));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([10])));
    }

    public function testEmptyWantedFalse(): void
    {
        $spec = new CategoryMembershipSpec([]);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([1, 2])));
    }

    public function testNullDataFalse(): void
    {
        $spec = new CategoryMembershipSpec([10]);
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor(null)));
    }

    public function testRejectsUnknownMode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CategoryMembershipSpec([1], 'sometimes');
    }
}
