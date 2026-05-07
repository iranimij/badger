<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\ConditionsTreeBuilder;
use Iranimij\Badger\Model\Evaluation\ConditionsTreeSerializer;
use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ConditionsTreeSerializerTest extends TestCase
{
    private ConditionsTreeBuilder $builder;
    private ConditionsTreeSerializer $serializer;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->builder = $om->get(ConditionsTreeBuilder::class);
        $this->serializer = $om->get(ConditionsTreeSerializer::class);
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $original = [
            'op' => 'AND',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                ['token' => 'price_range', 'args' => ['min' => 10, 'max' => 200]],
            ],
        ];

        $encoded = $this->serializer->encode($original);
        self::assertIsString($encoded);

        $decoded = $this->serializer->decode($encoded);
        self::assertSame($original, $decoded);
    }

    public function testEncodedTreeCanBeBuiltAndEvaluated(): void
    {
        $treeArray = [
            'op' => 'OR',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                ['token' => 'price_range', 'args' => ['min' => 0, 'max' => 1000]],
            ],
        ];

        $encoded = $this->serializer->encode($treeArray);
        $decoded = $this->serializer->decode($encoded);
        $spec = $this->builder->build($decoded);

        $product = Bootstrap::getObjectManager()->create(ProductInterface::class);
        $product->setPrice(50.0);
        $product->setSpecialPrice(null);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        self::assertTrue($spec->isSatisfiedBy($ctx));
    }

    public function testDecodeInvalidJsonReturnsDefault(): void
    {
        $decoded = $this->serializer->decode('not-valid-json{{{');
        self::assertSame('AND', $decoded['op']);
        self::assertSame([], $decoded['children']);
    }

    public function testNestedTreeRoundTrip(): void
    {
        $nested = [
            'op' => 'AND',
            'children' => [
                ['token' => 'on_sale', 'args' => []],
                [
                    'op' => 'OR',
                    'children' => [
                        ['token' => 'price_range', 'args' => ['min' => 0, 'max' => 100]],
                        ['token' => 'category_in', 'args' => ['values' => ['3', '5']]],
                    ],
                ],
            ],
        ];

        $encoded = $this->serializer->encode($nested);
        $decoded = $this->serializer->decode($encoded);

        self::assertSame('AND', $decoded['op']);
        self::assertCount(2, $decoded['children']);
        self::assertSame('OR', $decoded['children'][1]['op']);
    }

    public function testToSpecificationBuildsWorkingSpec(): void
    {
        $payload = $this->serializer->encode([
            'op' => 'AND',
            'children' => [
                ['token' => 'price_range', 'args' => ['min' => 0, 'max' => 500]],
            ],
        ]);

        $spec = $this->serializer->toSpecification($payload);

        $product = Bootstrap::getObjectManager()->create(ProductInterface::class);
        $product->setPrice(100.0);

        $ctx = new EvaluationContext($product, 1, 0, 1);
        self::assertTrue($spec->isSatisfiedBy($ctx));
    }
}
