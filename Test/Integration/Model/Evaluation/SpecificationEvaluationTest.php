<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\ConditionsTreeBuilder;
use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class SpecificationEvaluationTest extends TestCase
{
    private ConditionsTreeBuilder $treeBuilder;

    protected function setUp(): void
    {
        $this->treeBuilder = Bootstrap::getObjectManager()->get(ConditionsTreeBuilder::class);
    }

    private function makeProduct(): ProductInterface
    {
        return Bootstrap::getObjectManager()->create(ProductInterface::class);
    }

    public function testOnSaleSpecMatchesDiscountedProduct(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(100.0);
        $product->setSpecialPrice(80.0);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+1 day')));

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build(['token' => 'on_sale', 'args' => []]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testOnSaleSpecDoesNotMatchFullPriceProduct(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(100.0);
        $product->setSpecialPrice(null);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build(['token' => 'on_sale', 'args' => []]);

        self::assertFalse($spec->isSatisfiedBy($ctx));
    }

    public function testAttributeEqualsSpec(): void
    {
        $product = $this->makeProduct();
        $product->setData('status', 1);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'token' => 'attr_eq',
            'args' => ['attribute' => 'status', 'value' => 1],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testAttributeEqualsSpecMismatch(): void
    {
        $product = $this->makeProduct();
        $product->setData('status', 1);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'token' => 'attr_eq',
            'args' => ['attribute' => 'status', 'value' => 2],
        ]);

        self::assertFalse($spec->isSatisfiedBy($ctx));
    }

    public function testPriceRangeSpecMatches(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(50.0);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'token' => 'price_range',
            'args' => ['min' => 10, 'max' => 100],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testPriceRangeSpecOutOfRange(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(200.0);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'token' => 'price_range',
            'args' => ['min' => 10, 'max' => 100],
        ]);

        self::assertFalse($spec->isSatisfiedBy($ctx));
    }

    public function testCompositeAndRequiresBothTrue(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(50.0);
        $product->setSpecialPrice(40.0);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+1 day')));
        $product->setData('status', 1);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'op' => 'AND',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                ['token' => 'attr_eq', 'args' => ['attribute' => 'status', 'value' => 1]],
            ],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testCompositeAndFailsIfOneFalse(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(50.0);
        $product->setSpecialPrice(null); // not on sale

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'op' => 'AND',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                ['token' => 'price_range', 'args' => ['min' => 0, 'max' => 100]],
            ],
        ]);

        self::assertFalse($spec->isSatisfiedBy($ctx));
    }

    public function testCompositeOrPassesIfOneTrue(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(200.0);
        $product->setSpecialPrice(null);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'op' => 'OR',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                ['token' => 'price_range', 'args' => ['min' => 100, 'max' => 500]],
            ],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testQuantityRangeSpec(): void
    {
        $product = $this->makeProduct();

        $ctx = new EvaluationContext($product, 1, 0, 100);
        $spec = $this->treeBuilder->build([
            'token' => 'qty_range',
            'args' => ['min' => 50, 'max' => 200],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testCustomerGroupSpec(): void
    {
        $product = $this->makeProduct();

        $ctx = new EvaluationContext($product, 1, 1, 1);
        $spec = $this->treeBuilder->build([
            'token' => 'customer_group',
            'args' => ['groups' => [0, 1, 2]],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testStoreSpec(): void
    {
        $product = $this->makeProduct();

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'token' => 'store',
            'args' => ['stores' => [1]],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testNestedCompositeTree(): void
    {
        $product = $this->makeProduct();
        $product->setPrice(50.0);
        $product->setSpecialPrice(40.0);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+1 day')));

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build([
            'op' => 'AND',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                [
                    'op' => 'OR',
                    'children' => [
                        ['token' => 'price_range', 'args' => ['min' => 0, 'max' => 200]],
                        ['token' => 'store', 'args' => ['stores' => [99]]],
                    ],
                ],
            ],
        ]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testIsNewSpec(): void
    {
        $product = $this->makeProduct();
        $product->setNewsFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setNewsToDate(date('Y-m-d', strtotime('+30 days')));

        $ctx = new EvaluationContext($product, 1, 0, 1);
        $spec = $this->treeBuilder->build(['token' => 'is_new', 'args' => []]);

        self::assertTrue($spec->isSatisfiedBy($ctx));
    }
}
