<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterfaceFactory;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Badger;
use Iranimij\Badger\Model\BadgerRepository;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Iranimij\Badger\Model\ResourceModel\Badger\Collection;
use Iranimij\Badger\Model\ResourceModel\Badger\CollectionFactory;
use Iranimij\Badger\Model\ResourceModel\CustomerGroupLink;
use Iranimij\Badger\Model\ResourceModel\StoreLink;
use Iranimij\Badger\Model\ResourceModel\Tooltip as TooltipResource;
use Iranimij\Badger\Model\ResourceModel\Visual as VisualResource;
use Iranimij\Badger\Model\Tooltip;
use Iranimij\Badger\Model\Visual;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BadgerRepositoryTest extends TestCase
{
    private BadgerResource&MockObject $badgerResource;
    private CollectionFactory&MockObject $collectionFactory;
    private VisualResource&MockObject $visualResource;
    private TooltipResource&MockObject $tooltipResource;
    private StoreLink&MockObject $storeLink;
    private CustomerGroupLink&MockObject $cgLink;
    private BadgerSearchResultsInterfaceFactory&MockObject $searchResultsFactory;
    private CollectionProcessorInterface&MockObject $collectionProcessor;
    private ObjectManagerInterface&MockObject $objectManager;
    private BadgerRepository $repository;
    private Context $context;
    private Registry $registry;

    protected function setUp(): void
    {
        $this->badgerResource = $this->createMock(BadgerResource::class);
        $this->badgerResource->method('getIdFieldName')->willReturn('badger_id');
        $this->visualResource = $this->createMock(VisualResource::class);
        $this->visualResource->method('getIdFieldName')->willReturn('entity_id');
        $this->tooltipResource = $this->createMock(TooltipResource::class);
        $this->tooltipResource->method('getIdFieldName')->willReturn('entity_id');
        $this->storeLink = $this->createMock(StoreLink::class);
        $this->cgLink = $this->createMock(CustomerGroupLink::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->searchResultsFactory = $this->createMock(BadgerSearchResultsInterfaceFactory::class);

        $eventManager = $this->createMock(ManagerInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->context->method('getEventDispatcher')->willReturn($eventManager);
        $this->registry = $this->createMock(Registry::class);

        $this->objectManager->method('create')->willReturnCallback(function (string $class) {
            return match ($class) {
                Badger::class, BadgerInterface::class => new Badger($this->context, $this->registry, $this->badgerResource),
                Visual::class, VisualInterface::class => new Visual($this->context, $this->registry, $this->visualResource),
                Tooltip::class, TooltipInterface::class => new Tooltip($this->context, $this->registry, $this->tooltipResource),
                default => throw new \RuntimeException("Unexpected class: $class"),
            };
        });

        $this->repository = new BadgerRepository(
            $this->badgerResource,
            $this->collectionFactory,
            $this->visualResource,
            $this->tooltipResource,
            $this->storeLink,
            $this->cgLink,
            $this->searchResultsFactory,
            $this->collectionProcessor,
            $this->objectManager
        );
    }

    public function testGetByIdThrowsWhenNotFound(): void
    {
        $this->badgerResource->expects(self::once())
            ->method('load')
            ->willReturnCallback(function (Badger $badger) {
                return $badger;
            });

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById(999);
    }

    public function testGetByIdLoadsAllRelations(): void
    {
        $this->badgerResource->method('load')->willReturnCallback(function (Badger $badger, int $id) {
            $badger->setData(BadgerInterface::BADGER_ID, $id);
            $badger->setData(BadgerInterface::NAME, 'Loaded');
            return $badger;
        });
        $this->storeLink->method('fetch')->with(42)->willReturn([1, 2]);
        $this->cgLink->method('fetch')->with(42)->willReturn([0]);
        $this->visualResource->method('fetchForBadger')->with(42)->willReturn([[
            VisualInterface::BADGER_ID => 42,
            VisualInterface::SURFACE => 1,
            VisualInterface::SHAPE_KIND => 0,
            VisualInterface::PLACEMENT => 2,
            VisualInterface::LABEL_TEXT => 'SALE',
            VisualInterface::IMAGE_PATH => null,
            VisualInterface::REDIRECT_URL => null,
            VisualInterface::ALT_TEXT => null,
            VisualInterface::SIZE_PERCENT => 25,
            VisualInterface::STYLE_PAYLOAD => null,
        ]]);
        $this->tooltipResource->method('loadByBadger')->with(42)->willReturn([
            TooltipInterface::BADGER_ID => 42,
            TooltipInterface::IS_ENABLED => 1,
            TooltipInterface::BODY_TEXT => 'hover',
            TooltipInterface::BACKGROUND_COLOR => null,
            TooltipInterface::TEXT_COLOR => null,
        ]);

        $badger = $this->repository->getById(42);

        self::assertSame(42, $badger->getBadgerId());
        self::assertSame([1, 2], $badger->getStoreIds());
        self::assertSame([0], $badger->getCustomerGroupIds());
        self::assertCount(1, $badger->getVisuals());
        self::assertSame('SALE', $badger->getVisuals()[0]->getLabelText());
        self::assertNotNull($badger->getTooltip());
        self::assertSame('hover', $badger->getTooltip()->getBodyText());
    }

    public function testSavePersistsScalarVisualsAndRelations(): void
    {
        $badger = new Badger($this->context, $this->registry, $this->badgerResource);
        $badger->setName('Spring')->setPriority(1)->setIsEnabled(true);
        $badger->setStoreIds([1]);
        $badger->setCustomerGroupIds([0]);

        $this->badgerResource->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (Badger $m) {
                $m->setData(BadgerInterface::BADGER_ID, 7);
                return $m;
            });

        $this->storeLink->expects(self::once())->method('sync')->with(7, [1]);
        $this->cgLink->expects(self::once())->method('sync')->with(7, [0]);
        $this->visualResource->expects(self::once())->method('deleteForBadger')->with(7);
        $this->tooltipResource->expects(self::once())->method('deleteForBadger')->with(7);

        // getById follow-up
        $this->badgerResource->method('load')->willReturnCallback(function (Badger $b, int $id) {
            $b->setData(BadgerInterface::BADGER_ID, $id);
            return $b;
        });
        $this->storeLink->method('fetch')->willReturn([1]);
        $this->cgLink->method('fetch')->willReturn([0]);
        $this->visualResource->method('fetchForBadger')->willReturn([]);
        $this->tooltipResource->method('loadByBadger')->willReturn(null);

        $saved = $this->repository->save($badger);
        self::assertSame(7, $saved->getBadgerId());
    }

    public function testGetListDelegatesToCollectionProcessor(): void
    {
        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $collection = $this->createMock(Collection::class);
        $this->collectionFactory->method('create')->willReturn($collection);
        $this->collectionProcessor->expects(self::once())->method('process')->with($criteria, $collection);

        $item = new Badger($this->context, $this->registry, $this->badgerResource);
        $item->setData(BadgerInterface::BADGER_ID, 11);
        $collection->method('getItems')->willReturn([$item]);
        $collection->method('getSize')->willReturn(1);

        $results = $this->createMock(BadgerSearchResultsInterface::class);
        $this->searchResultsFactory->method('create')->willReturn($results);
        $results->expects(self::once())->method('setSearchCriteria')->with($criteria);
        $results->expects(self::once())->method('setTotalCount')->with(1);
        $results->expects(self::once())->method('setItems');

        $this->badgerResource->method('load')->willReturnCallback(function (Badger $b, int $id) {
            $b->setData(BadgerInterface::BADGER_ID, $id);
            return $b;
        });
        $this->storeLink->method('fetch')->willReturn([]);
        $this->cgLink->method('fetch')->willReturn([]);
        $this->visualResource->method('fetchForBadger')->willReturn([]);
        $this->tooltipResource->method('loadByBadger')->willReturn(null);

        $this->repository->getList($criteria);
    }
}
