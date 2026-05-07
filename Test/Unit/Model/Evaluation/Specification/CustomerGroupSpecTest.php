<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\CustomerGroupSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class CustomerGroupSpecTest extends TestCase
{
    private function ctxGroup(int $groupId): EvaluationContext
    {
        return new EvaluationContext($this->createMock(Product::class), 1, $groupId);
    }

    public function testMatches(): void
    {
        $spec = new CustomerGroupSpec([0, 1, 2]);
        self::assertTrue($spec->isSatisfiedBy($this->ctxGroup(1)));
    }

    public function testMiss(): void
    {
        $spec = new CustomerGroupSpec([0, 1, 2]);
        self::assertFalse($spec->isSatisfiedBy($this->ctxGroup(3)));
    }

    public function testEmptyListNeverMatches(): void
    {
        $spec = new CustomerGroupSpec([]);
        self::assertFalse($spec->isSatisfiedBy($this->ctxGroup(0)));
    }
}
