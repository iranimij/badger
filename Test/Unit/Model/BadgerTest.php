<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model;

use Iranimij\Badger\Model\Badger;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Iranimij\Badger\Model\ResourceModel\Tooltip as TooltipResource;
use Iranimij\Badger\Model\ResourceModel\Visual as VisualResource;
use Iranimij\Badger\Model\Tooltip;
use Iranimij\Badger\Model\Visual;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;

class BadgerTest extends TestCase
{
    private function makeModel(): Badger
    {
        $eventManager = $this->createMock(ManagerInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($eventManager);
        $registry = $this->createMock(Registry::class);
        $resource = $this->createMock(BadgerResource::class);
        $resource->method('getIdFieldName')->willReturn('badger_id');
        return new Badger($context, $registry, $resource);
    }

    public function testScalarSettersAndGetters(): void
    {
        $badger = $this->makeModel();
        $badger->setName('Spring Sale')
            ->setIsEnabled(true)
            ->setPriority(7)
            ->setIsExclusive(true)
            ->setUseForParent(true)
            ->setActiveFrom('2026-04-01 00:00:00')
            ->setActiveTo('2026-05-01 00:00:00')
            ->setConditionsPayload('{"op":"and","children":[]}');

        self::assertSame('Spring Sale', $badger->getName());
        self::assertTrue($badger->isEnabled());
        self::assertSame(7, $badger->getPriority());
        self::assertTrue($badger->isExclusive());
        self::assertTrue($badger->getUseForParent());
        self::assertSame('2026-04-01 00:00:00', $badger->getActiveFrom());
        self::assertSame('2026-05-01 00:00:00', $badger->getActiveTo());
        self::assertSame('{"op":"and","children":[]}', $badger->getConditionsPayload());
    }

    public function testStoreAndGroupIdsNormalizeToInt(): void
    {
        $badger = $this->makeModel();
        $badger->setStoreIds(['1', '2', 3]);
        $badger->setCustomerGroupIds(['0', '1']);

        self::assertSame([1, 2, 3], $badger->getStoreIds());
        self::assertSame([0, 1], $badger->getCustomerGroupIds());
    }

    public function testIdentitiesUseCacheTag(): void
    {
        $badger = $this->makeModel();
        self::assertSame([Badger::CACHE_TAG], $badger->getIdentities());

        $badger->setBadgerId(42);
        self::assertSame([Badger::CACHE_TAG . '_42'], $badger->getIdentities());
    }

    public function testVisualsAndTooltipSetGet(): void
    {
        $badger = $this->makeModel();
        self::assertSame([], $badger->getVisuals());
        self::assertNull($badger->getTooltip());

        $eventManager = $this->createMock(ManagerInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($eventManager);
        $registry = $this->createMock(Registry::class);
        $visualResource = $this->createMock(VisualResource::class);
        $visualResource->method('getIdFieldName')->willReturn('entity_id');
        $tooltipResource = $this->createMock(TooltipResource::class);
        $tooltipResource->method('getIdFieldName')->willReturn('entity_id');

        $visual = new Visual($context, $registry, $visualResource);
        $tooltip = new Tooltip($context, $registry, $tooltipResource);
        $badger->setVisuals([$visual]);
        $badger->setTooltip($tooltip);

        self::assertSame([$visual], $badger->getVisuals());
        self::assertSame($tooltip, $badger->getTooltip());
    }
}
