<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface;
use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\BestsellerSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class BestsellerSpecTest extends TestCase
{
    private function product(?int $id): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($id);
        return $product;
    }

    public function testDelegatesToLookup(): void
    {
        $lookup = $this->createMock(BestsellerLookupInterface::class);
        $lookup->expects(self::once())
            ->method('isBestseller')
            ->with(42, 1, 25)
            ->willReturn(true);

        $spec = new BestsellerSpec($lookup, 25);
        self::assertTrue($spec->isSatisfiedBy(new EvaluationContext($this->product(42), 1, 0)));
    }

    public function testMissingProductIdFalse(): void
    {
        $lookup = $this->createMock(BestsellerLookupInterface::class);
        $lookup->expects(self::never())->method('isBestseller');

        $spec = new BestsellerSpec($lookup);
        self::assertFalse($spec->isSatisfiedBy(new EvaluationContext($this->product(null), 1, 0)));
    }
}
