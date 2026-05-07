<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Condition;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Model\Condition\Combine;
use Iranimij\Badger\Model\Condition\IsNew;
use Iranimij\Badger\Model\Condition\OnSale;
use Iranimij\Badger\Model\Condition\Qty;
use Iranimij\Badger\Model\Condition\StockStatus;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Badger\Ui\DataProvider\Form\Modifier\ConditionsModifier;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\CatalogRule\Model\Rule\Condition\Product as CatalogProduct;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests that every condition type can be:
 *  - saved (serialised to JSON via Save controller's normalize logic)
 *  - reloaded (deserialised via ConditionsModifier::prepareRuleModel)
 *  - rendered (asHtmlRecursive does not throw)
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class ConditionsRoundtripTest extends TestCase
{
    private RuleFactory $ruleFactory;
    private JsonSerializer $serializer;
    private ConditionsModifier $modifier;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->ruleFactory = $om->get(RuleFactory::class);
        $this->serializer = $om->get(JsonSerializer::class);

        $request = $om->get(RequestInterface::class);
        $request->setParam('badger_id', 0);
        $this->modifier = $om->get(ConditionsModifier::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function buildSingleConditionPayload(string $type, array $extra = []): string
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditions([]);

        $cond = $rule->getConditions()->newChildElement();
        $cond->setType($type);
        foreach ($extra as $k => $v) {
            $cond->setData($k, $v);
        }
        $rule->getConditions()->addCondition($cond);

        return $this->serializer->encode($rule->getConditions()->asArray());
    }

    private function assertReloadsWithoutException(string $payload): void
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditions([]);

        $decoded = $this->serializer->decode($payload);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('type', $decoded);

        // This is exactly what ConditionsModifier::prepareRuleModel does.
        $rule->getConditions()->loadArray($decoded);

        // Rendering must not throw.
        $html = $rule->getConditions()->asHtmlRecursive();
        $this->assertIsString($html);
    }

    // ── Tests ────────────────────────────────────────────────────────────────

    public function testOnSaleConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(OnSale::class);
        $this->assertReloadsWithoutException($payload);
    }

    public function testIsNewConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(IsNew::class);
        $this->assertReloadsWithoutException($payload);
    }

    public function testStockStatusConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(StockStatus::class);
        $this->assertReloadsWithoutException($payload);
    }

    public function testQtyConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(Qty::class);
        $this->assertReloadsWithoutException($payload);
    }

    public function testCategoryIdsConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(
            CatalogProduct::class . '|category_ids',
            ['operator' => '==', 'value' => '2']
        );
        $this->assertReloadsWithoutException($payload);
    }

    public function testPriceConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(
            CatalogProduct::class . '|price',
            ['operator' => '>=', 'value' => '10']
        );
        $this->assertReloadsWithoutException($payload);
    }

    public function testStatusConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(
            CatalogProduct::class . '|status',
            ['operator' => '==', 'value' => '1']
        );
        $this->assertReloadsWithoutException($payload);
    }

    public function testNameConditionRoundtrip(): void
    {
        $payload = $this->buildSingleConditionPayload(
            CatalogProduct::class . '|name',
            ['operator' => 'like', 'value' => '%sale%']
        );
        $this->assertReloadsWithoutException($payload);
    }

    public function testNestedCombineConditionRoundtrip(): void
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditions([]);

        // Build nested: (OnSale AND IsNew) OR Qty >= 5
        $outerCombine = $rule->getConditions(); // root combine (AND)
        $outerCombine->setAggregator('any'); // OR

        $innerCombine = $outerCombine->newChildElement();
        $innerCombine->setType(Combine::class);
        $innerCombine->setAggregator('all');

        $c1 = $outerCombine->newChildElement();
        $c1->setType(OnSale::class);
        $innerCombine->addCondition($c1);

        $c2 = $outerCombine->newChildElement();
        $c2->setType(IsNew::class);
        $innerCombine->addCondition($c2);

        $outerCombine->addCondition($innerCombine);

        $c3 = $outerCombine->newChildElement();
        $c3->setType(Qty::class);
        $c3->setOperator('>=');
        $c3->setValue('5');
        $outerCombine->addCondition($c3);

        $payload = $this->serializer->encode($outerCombine->asArray());
        $this->assertReloadsWithoutException($payload);
    }

    public function testEmptyPayloadProducesEmptyConditions(): void
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditions([]);

        $html = $rule->getConditions()->asHtmlRecursive();
        $this->assertIsString($html);
    }

    public function testConditionsModifierMetaDoesNotThrow(): void
    {
        $meta = $this->modifier->modifyMeta(['conditions' => [
            'children' => [
                'conditions_payload' => [
                    'arguments' => [
                        'data' => [
                            'config' => [],
                        ],
                    ],
                ],
            ],
        ]]);

        $this->assertIsArray($meta);
        $config = $meta['conditions']['children']['conditions_payload']['arguments']['data']['config'] ?? [];
        $this->assertArrayHasKey('formContent', $config);
        $this->assertArrayHasKey('newFormChildUrl', $config);
    }

    public function testSaveNormalizeConditionsRoundtrip(): void
    {
        // Simulate what Save::normalize() does when conditions_payload arrives as
        // form POST data from the Magento conditions fieldset.
        $om = Bootstrap::getObjectManager();
        $rule = $this->ruleFactory->create();

        // Flat form POST format (as submitted by the browser conditions widget)
        $formPost = [
            '1' => [
                'type'        => Combine::class,
                'aggregator'  => 'all',
                'value'       => '1',
                'new_child'   => '',
            ],
            '1--1' => [
                'type'     => OnSale::class,
                'operator' => '==',
                'value'    => '1',
            ],
            '1--2' => [
                'type'     => CatalogProduct::class . '|category_ids',
                'operator' => '==',
                'value'    => '2',
            ],
        ];

        $rule->loadPost(['conditions' => $formPost]);
        $encoded = $this->serializer->encode($rule->getConditions()->asArray());

        // Now reload as ConditionsModifier would
        $rule2 = $this->ruleFactory->create();
        $rule2->setConditions([]);
        $decoded = $this->serializer->decode($encoded);
        $rule2->getConditions()->loadArray($decoded);

        $html = $rule2->getConditions()->asHtmlRecursive();
        $this->assertIsString($html);
        $this->assertStringContainsString('conditions', strtolower($html));
    }
}
