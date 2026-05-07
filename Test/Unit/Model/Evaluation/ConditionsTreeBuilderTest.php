<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\ConditionsTreeBuilder;
use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Factory\AttributeEqualsFactory;
use Iranimij\Badger\Model\Evaluation\Factory\OnSaleFactory;
use Iranimij\Badger\Model\Evaluation\Specification\CompositeSpecification;
use Iranimij\Badger\Model\Evaluation\SpecificationRegistry;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class ConditionsTreeBuilderTest extends TestCase
{
    private ConditionsTreeBuilder $builder;

    protected function setUp(): void
    {
        $registry = new SpecificationRegistry([
            'attr_eq' => new AttributeEqualsFactory(),
            'on_sale' => new OnSaleFactory(),
        ]);
        $this->builder = new ConditionsTreeBuilder($registry);
    }

    public function testBuildsLeafFromToken(): void
    {
        $spec = $this->builder->build([
            'token' => 'attr_eq',
            'args' => ['attribute' => 'color', 'value' => 'red'],
        ]);

        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturn('red');
        self::assertTrue($spec->isSatisfiedBy(new EvaluationContext($product, 1, 0)));
    }

    public function testBuildsNestedComposite(): void
    {
        $spec = $this->builder->build([
            'op' => 'and',
            'children' => [
                ['token' => 'attr_eq', 'args' => ['attribute' => 'color', 'value' => 'red']],
                [
                    'op' => 'or',
                    'children' => [
                        ['token' => 'on_sale', 'args' => []],
                        ['token' => 'attr_eq', 'args' => ['attribute' => 'brand', 'value' => 'X']],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(CompositeSpecification::class, $spec);
        self::assertSame('and', $spec->getOperator());
        self::assertCount(2, $spec->getChildren());
    }

    public function testRejectsLeafWithoutToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->build(['args' => []]);
    }

    public function testRejectsNonArrayChild(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->build(['op' => 'and', 'children' => ['not-array']]);
    }
}
