<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\CompositeSpecification;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class CompositeSpecificationTest extends TestCase
{
    private function ctx(): EvaluationContext
    {
        return new EvaluationContext($this->createMock(Product::class), 1, 0);
    }

    private function stub(bool $result, int &$calls = 0): SpecificationInterface
    {
        return new class ($result, $calls) implements SpecificationInterface {
            public function __construct(private bool $result, private int &$calls)
            {
            }
            public function isSatisfiedBy(EvaluationContext $ctx): bool
            {
                $this->calls++;
                return $this->result;
            }
        };
    }

    public function testAndReturnsTrueForEmpty(): void
    {
        $spec = new CompositeSpecification(CompositeSpecification::OP_AND, []);
        self::assertTrue($spec->isSatisfiedBy($this->ctx()));
    }

    public function testOrReturnsFalseForEmpty(): void
    {
        $spec = new CompositeSpecification(CompositeSpecification::OP_OR, []);
        self::assertFalse($spec->isSatisfiedBy($this->ctx()));
    }

    public function testAndShortCircuitsOnFirstFalse(): void
    {
        $aCalls = 0;
        $bCalls = 0;
        $a = $this->stub(false, $aCalls);
        $b = $this->stub(true, $bCalls);
        $spec = new CompositeSpecification(CompositeSpecification::OP_AND, [$a, $b]);
        self::assertFalse($spec->isSatisfiedBy($this->ctx()));
        self::assertSame(1, $aCalls);
        self::assertSame(0, $bCalls);
    }

    public function testOrShortCircuitsOnFirstTrue(): void
    {
        $aCalls = 0;
        $bCalls = 0;
        $a = $this->stub(true, $aCalls);
        $b = $this->stub(true, $bCalls);
        $spec = new CompositeSpecification(CompositeSpecification::OP_OR, [$a, $b]);
        self::assertTrue($spec->isSatisfiedBy($this->ctx()));
        self::assertSame(1, $aCalls);
        self::assertSame(0, $bCalls);
    }

    public function testNestedTrees(): void
    {
        $dummy = 0;
        $t = $this->stub(true, $dummy);
        $f = $this->stub(false, $dummy);
        // (t AND f) OR (t AND t) -> true
        $nested = new CompositeSpecification(CompositeSpecification::OP_OR, [
            new CompositeSpecification(CompositeSpecification::OP_AND, [$t, $f]),
            new CompositeSpecification(CompositeSpecification::OP_AND, [$t, $t]),
        ]);
        self::assertTrue($nested->isSatisfiedBy($this->ctx()));
    }

    public function testRejectsUnsupportedOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CompositeSpecification('xor', []);
    }
}
