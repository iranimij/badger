<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Indexer;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use Iranimij\Badger\Model\Indexer\IndexBuilder;
use Iranimij\Badger\Model\Rule;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use PHPUnit\Framework\TestCase;

class IndexBuilderTest extends TestCase
{
    private AdapterInterface $adapter;
    private ResourceConnection $resource;
    private BadgerRepositoryInterface $badgerRepository;
    private ProductRepositoryInterface $productRepository;
    private StoreRepositoryInterface $storeRepository;
    private GroupRepositoryInterface $groupRepository;
    private SearchCriteriaBuilder $criteriaBuilder;
    private RuleFactory $ruleFactory;
    private JsonSerializer $jsonSerializer;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->resource->method('getConnection')->willReturn($this->adapter);
        $this->resource->method('getTableName')->willReturnArgument(0);

        $this->badgerRepository = $this->createMock(BadgerRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->storeRepository = $this->createMock(StoreRepositoryInterface::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->criteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->criteriaBuilder->method('addFilter')->willReturnSelf();
        $this->criteriaBuilder->method('create')->willReturn($this->createMock(SearchCriteria::class));
        $this->ruleFactory = $this->createMock(RuleFactory::class);
        $this->jsonSerializer = $this->createMock(JsonSerializer::class);
    }

    public function testRebuildAllTruncatesAndInserts(): void
    {
        $this->mockStores([1]);
        $this->mockGroups([0]);
        $this->mockProducts([42]);

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(7);
        $badger->method('isEnabled')->willReturn(true);
        $badger->method('getStoreIds')->willReturn([]);
        $badger->method('getCustomerGroupIds')->willReturn([]);
        $badger->method('getConditionsPayload')->willReturn(null);

        $this->mockRepoBadgers([$badger]);

        $this->adapter->expects(self::once())->method('delete')->with('iranimij_badger_index');
        $this->adapter->expects(self::once())
            ->method('insertMultiple')
            ->with('iranimij_badger_index', [
                ['badger_id' => 7, 'product_id' => 42, 'store_id' => 1, 'customer_group_id' => 0],
            ]);

        $this->builder()->rebuildAll();
    }

    public function testRebuildForProductsScopedDelete(): void
    {
        $this->mockStores([1]);
        $this->mockGroups([0]);

        $product = $this->createMock(ProductInterface::class);
        $product->method('getId')->willReturn(99);
        $this->productRepository->method('getById')->willReturn($product);

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(3);
        $badger->method('isEnabled')->willReturn(true);
        $badger->method('getStoreIds')->willReturn([]);
        $badger->method('getCustomerGroupIds')->willReturn([]);
        $badger->method('getConditionsPayload')->willReturn(null);
        $this->mockRepoBadgers([$badger]);

        $this->adapter->expects(self::once())
            ->method('delete')
            ->with('iranimij_badger_index', ['product_id IN (?)' => [99]]);

        $this->adapter->expects(self::once())->method('insertMultiple');

        $this->builder()->rebuildForProducts([99]);
    }

    public function testConditionFailureSkipsRow(): void
    {
        $this->mockStores([1]);
        $this->mockGroups([0]);

        // Use concrete Product (extends AbstractModel/DataObject) so Rule::validate() type check passes
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $product->method('getId')->willReturn(42);

        $results = $this->createMock(ProductSearchResultsInterface::class);
        $results->method('getItems')->willReturn([$product]);
        $this->productRepository->method('getList')->willReturn($results);

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(7);
        $badger->method('isEnabled')->willReturn(true);
        $badger->method('getStoreIds')->willReturn([]);
        $badger->method('getCustomerGroupIds')->willReturn([]);
        $badger->method('getConditionsPayload')->willReturn('{"type":"Combine"}');
        $this->mockRepoBadgers([$badger]);

        $rule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'loadPost'])
            ->getMock();
        $rule->method('validate')->willReturn(false);
        $this->ruleFactory->method('create')->willReturn($rule);
        $this->jsonSerializer->method('decodeArray')->willReturn(['type' => 'Combine']);

        $this->adapter->expects(self::once())->method('delete');
        $this->adapter->expects(self::never())->method('insertMultiple');

        $this->builder()->rebuildAll();
    }

    public function testRebuildForBadgersScopesById(): void
    {
        $this->mockStores([1]);
        $this->mockGroups([0]);
        $this->mockProducts([42]);

        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getBadgerId')->willReturn(7);
        $badger->method('isEnabled')->willReturn(true);
        $badger->method('getStoreIds')->willReturn([]);
        $badger->method('getCustomerGroupIds')->willReturn([]);
        $badger->method('getConditionsPayload')->willReturn(null);

        $this->badgerRepository->method('getById')->with(7)->willReturn($badger);

        $this->adapter->expects(self::once())
            ->method('delete')
            ->with('iranimij_badger_index', ['badger_id IN (?)' => [7]]);
        $this->adapter->expects(self::once())->method('insertMultiple');

        $this->builder()->rebuildForBadgers([7]);
    }

    private function builder(): IndexBuilder
    {
        return new IndexBuilder(
            $this->resource,
            $this->badgerRepository,
            $this->productRepository,
            $this->storeRepository,
            $this->groupRepository,
            $this->criteriaBuilder,
            $this->ruleFactory,
            $this->jsonSerializer
        );
    }

    /** @param int[] $ids */
    private function mockStores(array $ids): void
    {
        $stores = array_map(function (int $id): StoreInterface {
            $s = $this->createMock(StoreInterface::class);
            $s->method('getId')->willReturn($id);
            return $s;
        }, $ids);
        $this->storeRepository->method('getList')->willReturn($stores);
    }

    /** @param int[] $ids */
    private function mockGroups(array $ids): void
    {
        $groups = array_map(function (int $id): GroupInterface {
            $g = $this->createMock(GroupInterface::class);
            $g->method('getId')->willReturn($id);
            return $g;
        }, $ids);
        $results = $this->createMock(GroupSearchResultsInterface::class);
        $results->method('getItems')->willReturn($groups);
        $this->groupRepository->method('getList')->willReturn($results);
    }

    /** @param int[] $ids */
    private function mockProducts(array $ids): void
    {
        $products = array_map(function (int $id): ProductInterface {
            $p = $this->createMock(ProductInterface::class);
            $p->method('getId')->willReturn($id);
            return $p;
        }, $ids);
        $results = $this->createMock(ProductSearchResultsInterface::class);
        $results->method('getItems')->willReturn($products);
        $this->productRepository->method('getList')->willReturn($results);
    }

    /** @param BadgerInterface[] $badgers */
    private function mockRepoBadgers(array $badgers): void
    {
        $results = $this->createMock(BadgerSearchResultsInterface::class);
        $results->method('getItems')->willReturn($badgers);
        $this->badgerRepository->method('getList')->willReturn($results);
    }
}
