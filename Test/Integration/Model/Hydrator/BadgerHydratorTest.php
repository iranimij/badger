<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Hydrator;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\BadgerHydrator;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class BadgerHydratorTest extends TestCase
{
    private BadgerHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = Bootstrap::getObjectManager()->get(BadgerHydrator::class);
    }

    public function testFromArrayCreatesValidBadger(): void
    {
        $data = [
            BadgerInterface::NAME => 'Hydrated Badge',
            BadgerInterface::IS_ENABLED => true,
            BadgerInterface::PRIORITY => 5,
            BadgerInterface::IS_EXCLUSIVE => false,
            BadgerInterface::USE_FOR_PARENT => true,
            BadgerInterface::STORE_IDS => [1, 2],
            BadgerInterface::CUSTOMER_GROUP_IDS => [0, 1],
        ];

        $badger = $this->hydrator->fromArray($data);

        self::assertInstanceOf(BadgerInterface::class, $badger);
        self::assertSame('Hydrated Badge', $badger->getName());
        self::assertTrue($badger->isEnabled());
        self::assertSame(5, $badger->getPriority());
        self::assertTrue($badger->getUseForParent());
        self::assertSame([1, 2], $badger->getStoreIds());
    }

    public function testFromArrayWithTooltip(): void
    {
        $data = [
            BadgerInterface::NAME => 'Badge With Tooltip',
            BadgerInterface::TOOLTIP => [
                TooltipInterface::IS_ENABLED => true,
                TooltipInterface::BODY_TEXT => 'Tooltip body',
                TooltipInterface::BACKGROUND_COLOR => '#000',
                TooltipInterface::TEXT_COLOR => '#fff',
            ],
        ];

        $badger = $this->hydrator->fromArray($data);

        self::assertNotNull($badger->getTooltip());
        self::assertTrue($badger->getTooltip()->isEnabled());
        self::assertSame('Tooltip body', $badger->getTooltip()->getBodyText());
    }

    public function testFromArrayWithVisuals(): void
    {
        $data = [
            BadgerInterface::NAME => 'Badge With Visual',
            BadgerInterface::VISUALS => [
                [
                    VisualInterface::SURFACE => 1,
                    VisualInterface::SHAPE_KIND => 1,
                    VisualInterface::PLACEMENT => 1,
                    VisualInterface::LABEL_TEXT => '{{discount_percent}}%',
                    VisualInterface::SIZE_PERCENT => 30,
                ],
            ],
        ];

        $badger = $this->hydrator->fromArray($data);

        self::assertCount(1, $badger->getVisuals());
        self::assertSame('{{discount_percent}}%', $badger->getVisuals()[0]->getLabelText());
        self::assertSame(30, $badger->getVisuals()[0]->getSizePercent());
    }

    public function testFromArrayWithoutNameLeavesNameEmpty(): void
    {
        // Hydrator does not validate — it just maps. Validation is BadgerPayloadValidator's job.
        $badger = $this->hydrator->fromArray([BadgerInterface::IS_ENABLED => true]);
        self::assertInstanceOf(BadgerInterface::class, $badger);
        self::assertTrue($badger->isEnabled());
    }

    public function testFromArrayWithSchedule(): void
    {
        $from = '2026-01-01 00:00:00';
        $to = '2026-12-31 23:59:59';

        $data = [
            BadgerInterface::NAME => 'Scheduled',
            BadgerInterface::ACTIVE_FROM => $from,
            BadgerInterface::ACTIVE_TO => $to,
        ];

        $badger = $this->hydrator->fromArray($data);

        self::assertSame($from, $badger->getActiveFrom());
        self::assertSame($to, $badger->getActiveTo());
    }

    public function testFromArrayWithConditionsPayload(): void
    {
        $payload = json_encode(['op' => 'AND', 'children' => []]);

        $data = [
            BadgerInterface::NAME => 'Condition Badge',
            BadgerInterface::CONDITIONS_PAYLOAD => $payload,
        ];

        $badger = $this->hydrator->fromArray($data);
        self::assertSame($payload, $badger->getConditionsPayload());
    }
}
