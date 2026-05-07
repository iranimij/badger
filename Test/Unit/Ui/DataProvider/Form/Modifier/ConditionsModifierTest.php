<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Badger\Ui\DataProvider\Form\Modifier\ConditionsModifier;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class ConditionsModifierTest extends TestCase
{
    private function makeModifier(
        ?RequestInterface $request = null,
        ?BadgerRepositoryInterface $repository = null,
        ?RuleFactory $factory = null,
        ?UrlInterface $urlBuilder = null,
        ?JsonSerializer $jsonSerializer = null,
    ): ConditionsModifier {
        return new ConditionsModifier(
            $request ?? $this->createMock(RequestInterface::class),
            $repository ?? $this->createMock(BadgerRepositoryInterface::class),
            $factory ?? $this->createMock(RuleFactory::class),
            $urlBuilder ?? $this->createMock(UrlInterface::class),
            $jsonSerializer ?? $this->createMock(JsonSerializer::class),
        );
    }

    public function testModifyDataIsPassthrough(): void
    {
        $data = [1 => ['name' => 'Test']];
        self::assertSame($data, $this->makeModifier()->modifyData($data));
    }

    public function testModifyDataEmptyIsPassthrough(): void
    {
        self::assertSame([], $this->makeModifier()->modifyData([]));
    }

    public function testModifyMetaInjectsConditionsConfig(): void
    {
        $meta = [
            'conditions' => [
                'children' => [
                    'conditions_payload' => [
                        'arguments' => ['data' => ['config' => []]],
                    ],
                ],
            ],
        ];

        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->with('badger_id')->willReturn(0);

        $combine = $this->getMockBuilder(\Magento\Rule\Model\Condition\Combine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['asHtmlRecursive', 'getConditions'])
            ->getMock();
        $combine->method('asHtmlRecursive')->willReturn('<div></div>');
        $combine->method('getConditions')->willReturn([]);

        $ruleModel = $this->getMockBuilder(\Iranimij\Badger\Model\Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConditions', 'setConditions'])
            ->getMock();
        $ruleModel->method('getConditions')->willReturn($combine);
        $ruleModel->method('setConditions')->willReturnSelf();

        $factory = $this->createMock(RuleFactory::class);
        $factory->method('create')->willReturn($ruleModel);

        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getUrl')->willReturn('http://example.com/newChild');

        $modifier = $this->makeModifier(
            request: $request,
            factory: $factory,
            urlBuilder: $urlBuilder,
        );

        $result = $modifier->modifyMeta($meta);
        $config = $result['conditions']['children']['conditions_payload']['arguments']['data']['config'];
        self::assertArrayHasKey('formContent', $config);
        self::assertArrayHasKey('newFormChildUrl', $config);
        self::assertArrayHasKey('conditionsFormId', $config);
        self::assertSame(ConditionsModifier::CONDITIONS_FIELDSET_ID, $config['conditionsFormId']);
    }
}
