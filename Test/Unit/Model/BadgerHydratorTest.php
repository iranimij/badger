<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Badger;
use Iranimij\Badger\Model\BadgerHydrator;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Iranimij\Badger\Model\ResourceModel\Tooltip as TooltipResource;
use Iranimij\Badger\Model\ResourceModel\Visual as VisualResource;
use Iranimij\Badger\Model\Tooltip;
use Iranimij\Badger\Model\Visual;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;

class BadgerHydratorTest extends TestCase
{
    private BadgerHydrator $hydrator;

    protected function setUp(): void
    {
        $event = $this->createMock(ManagerInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($event);
        $registry = $this->createMock(Registry::class);

        $badgerResource = $this->createMock(BadgerResource::class);
        $badgerResource->method('getIdFieldName')->willReturn('badger_id');
        $visualResource = $this->createMock(VisualResource::class);
        $visualResource->method('getIdFieldName')->willReturn('entity_id');
        $tooltipResource = $this->createMock(TooltipResource::class);
        $tooltipResource->method('getIdFieldName')->willReturn('entity_id');

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->willReturnCallback(
            function (string $class) use ($context, $registry, $badgerResource, $visualResource, $tooltipResource) {
                return match ($class) {
                    BadgerInterface::class => new Badger($context, $registry, $badgerResource),
                    VisualInterface::class => new Visual($context, $registry, $visualResource),
                    TooltipInterface::class => new Tooltip($context, $registry, $tooltipResource),
                    default => throw new \RuntimeException("Unexpected class: $class"),
                };
            }
        );

        $this->hydrator = new BadgerHydrator($objectManager);
    }

    public function testFromArrayBuildsScalarFields(): void
    {
        $badger = $this->hydrator->fromArray([
            BadgerInterface::NAME => 'Black Friday',
            BadgerInterface::IS_ENABLED => true,
            BadgerInterface::PRIORITY => 5,
            BadgerInterface::STORE_IDS => [1, 2],
            BadgerInterface::CUSTOMER_GROUP_IDS => [0, 1],
            BadgerInterface::CONDITIONS_PAYLOAD => '{"op":"and","children":[]}',
        ]);

        self::assertSame('Black Friday', $badger->getName());
        self::assertTrue($badger->isEnabled());
        self::assertSame(5, $badger->getPriority());
        self::assertSame([1, 2], $badger->getStoreIds());
        self::assertSame([0, 1], $badger->getCustomerGroupIds());
    }

    public function testFromArrayBuildsVisualsAndTooltip(): void
    {
        $badger = $this->hydrator->fromArray([
            BadgerInterface::NAME => 'Has visuals',
            BadgerInterface::VISUALS => [
                [
                    VisualInterface::SURFACE => 1,
                    VisualInterface::SHAPE_KIND => 2,
                    VisualInterface::PLACEMENT => 0,
                    VisualInterface::LABEL_TEXT => 'SALE',
                    VisualInterface::SIZE_PERCENT => 30,
                ],
            ],
            BadgerInterface::TOOLTIP => [
                TooltipInterface::IS_ENABLED => true,
                TooltipInterface::BODY_TEXT => 'Hover me',
                TooltipInterface::BACKGROUND_COLOR => '#000',
                TooltipInterface::TEXT_COLOR => '#fff',
            ],
        ]);

        self::assertCount(1, $badger->getVisuals());
        $visual = $badger->getVisuals()[0];
        self::assertSame(1, $visual->getSurface());
        self::assertSame(2, $visual->getShapeKind());
        self::assertSame('SALE', $visual->getLabelText());
        self::assertSame(30, $visual->getSizePercent());

        self::assertNotNull($badger->getTooltip());
        self::assertSame('Hover me', $badger->getTooltip()->getBodyText());
        self::assertSame('#000', $badger->getTooltip()->getBackgroundColor());
    }

    public function testToArrayRoundTrip(): void
    {
        $payload = [
            BadgerInterface::BADGER_ID => 42,
            BadgerInterface::NAME => 'Roundtrip',
            BadgerInterface::IS_ENABLED => true,
            BadgerInterface::PRIORITY => 3,
            BadgerInterface::IS_EXCLUSIVE => false,
            BadgerInterface::USE_FOR_PARENT => true,
            BadgerInterface::ACTIVE_FROM => '2026-04-01 00:00:00',
            BadgerInterface::ACTIVE_TO => null,
            BadgerInterface::CONDITIONS_PAYLOAD => '{"op":"and"}',
            BadgerInterface::STORE_IDS => [1],
            BadgerInterface::CUSTOMER_GROUP_IDS => [0, 1],
            BadgerInterface::VISUALS => [[
                VisualInterface::SURFACE => 0,
                VisualInterface::SHAPE_KIND => 0,
                VisualInterface::PLACEMENT => 0,
                VisualInterface::LABEL_TEXT => 'X',
                VisualInterface::IMAGE_PATH => null,
                VisualInterface::REDIRECT_URL => null,
                VisualInterface::ALT_TEXT => null,
                VisualInterface::SIZE_PERCENT => 25,
                VisualInterface::STYLE_PAYLOAD => null,
                VisualInterface::BADGER_ID => null,
            ]],
            BadgerInterface::TOOLTIP => [
                TooltipInterface::BADGER_ID => null,
                TooltipInterface::IS_ENABLED => true,
                TooltipInterface::BODY_TEXT => 'tip',
                TooltipInterface::BACKGROUND_COLOR => null,
                TooltipInterface::TEXT_COLOR => null,
            ],
        ];

        $badger = $this->hydrator->fromArray($payload);
        $out = $this->hydrator->toArray($badger);

        self::assertSame($payload[BadgerInterface::NAME], $out[BadgerInterface::NAME]);
        self::assertSame(42, $out[BadgerInterface::BADGER_ID]);
        self::assertEquals($payload[BadgerInterface::VISUALS][0], $out[BadgerInterface::VISUALS][0]);
        self::assertEquals($payload[BadgerInterface::TOOLTIP], $out[BadgerInterface::TOOLTIP]);
    }
}
