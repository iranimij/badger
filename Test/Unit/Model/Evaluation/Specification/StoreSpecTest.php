<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\StoreSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class StoreSpecTest extends TestCase
{
    private function ctxStore(int $storeId): EvaluationContext
    {
        return new EvaluationContext($this->createMock(Product::class), $storeId, 0);
    }

    public function testMatches(): void
    {
        $spec = new StoreSpec([1, 2]);
        self::assertTrue($spec->isSatisfiedBy($this->ctxStore(2)));
    }

    public function testMiss(): void
    {
        $spec = new StoreSpec([1, 2]);
        self::assertFalse($spec->isSatisfiedBy($this->ctxStore(3)));
    }
}
