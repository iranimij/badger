<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Indexer;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\Indexer\BadgerIndexer;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class BadgerIndexerTest extends TestCase
{
    private BadgerIndexer $indexer;
    private BadgerRepositoryInterface $repository;
    private ResourceConnection $resource;
    private ProductRepositoryInterface $productRepository;
    private ?int $createdProductId = null;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->indexer = $om->get(BadgerIndexer::class);
        $this->repository = $om->get(BadgerRepositoryInterface::class);
        $this->resource = $om->get(ResourceConnection::class);
        $this->productRepository = $om->get(ProductRepositoryInterface::class);

        /** @var ProductInterfaceFactory $factory */
        $factory = $om->get(ProductInterfaceFactory::class);
        $product = $factory->create();
        $product->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Badger Indexer Test Product')
            ->setSku('badger-indexer-test')
            ->setPrice(100.00)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['qty' => 100, 'is_in_stock' => 1]);
        $saved = $this->productRepository->save($product);
        $this->createdProductId = (int) $saved->getId();
    }

    protected function tearDown(): void
    {
        if ($this->createdProductId !== null) {
            /** @var Registry $registry */
            $registry = Bootstrap::getObjectManager()->get(Registry::class);
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', true);
            try {
                $this->productRepository->deleteById('badger-indexer-test');
            } catch (\Exception $e) {
                // already gone
            }
            $registry->unregister('isSecureArea');
            $this->createdProductId = null;
        }
    }

    public function testFullReindexCompletesWithoutException(): void
    {
        $badger = $this->makeBadger('Index Test Badge');
        $badger->setConditionsPayload(json_encode([
            'op' => 'AND',
            'children' => [['token' => 'on_sale', 'args' => []]],
        ]));
        $this->repository->save($badger);

        $this->indexer->executeFull();

        // No exception = pass. Also verify index table is accessible.
        $conn = $this->resource->getConnection();
        $table = $this->resource->getTableName('iranimij_badger_index');
        self::assertTrue($conn->isTableExists($table));
    }

    public function testFullReindexIndexesSaleProduct(): void
    {
        $product = $this->productRepository->get('badger-indexer-test');
        $product->setSpecialPrice($product->getPrice() * 0.8);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+30 day')));
        $this->productRepository->save($product);

        $badger = $this->makeBadger('Sale Badge');
        $badger->setIsEnabled(true);
        $badger->setConditionsPayload(json_encode([
            'op' => 'AND',
            'children' => [['token' => 'on_sale', 'args' => []]],
        ]));
        $saved = $this->repository->save($badger);

        $this->indexer->executeFull();

        $conn = $this->resource->getConnection();
        $table = $this->resource->getTableName('iranimij_badger_index');
        $count = $conn->fetchOne(
            "SELECT COUNT(*) FROM {$table} WHERE badger_id = ? AND product_id = ?",
            [$saved->getBadgerId(), $product->getId()]
        );

        self::assertGreaterThan(0, (int) $count, 'On-sale product should be in index for on_sale badger');
    }

    public function testDisabledBadgerNotIndexed(): void
    {
        $product = $this->productRepository->get('badger-indexer-test');
        $product->setSpecialPrice($product->getPrice() * 0.8);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+30 day')));
        $this->productRepository->save($product);

        $badger = $this->makeBadger('Disabled Badge');
        $badger->setIsEnabled(false);
        $badger->setConditionsPayload(json_encode([
            'op' => 'AND',
            'children' => [['token' => 'on_sale', 'args' => []]],
        ]));
        $saved = $this->repository->save($badger);

        $this->indexer->executeFull();

        $conn = $this->resource->getConnection();
        $table = $this->resource->getTableName('iranimij_badger_index');
        $count = $conn->fetchOne(
            "SELECT COUNT(*) FROM {$table} WHERE badger_id = ?",
            [$saved->getBadgerId()]
        );

        self::assertSame(0, (int) $count, 'Disabled badger should produce no index entries');
    }

    public function testExecuteListReindexesSpecificProducts(): void
    {
        $product = $this->productRepository->get('badger-indexer-test');

        $badger = $this->makeBadger('List Badge');
        $badger->setIsEnabled(true);
        $this->repository->save($badger);

        // Should not throw
        $this->indexer->executeList([(int) $product->getId()]);
        self::assertTrue(true);
    }

    private function makeBadger(string $name): BadgerInterface
    {
        $badger = Bootstrap::getObjectManager()->create(BadgerInterface::class);
        $badger->setName($name);
        $badger->setIsEnabled(true);
        $badger->setPriority(0);
        $badger->setIsExclusive(false);
        $badger->setUseForParent(false);
        $badger->setStoreIds([0]);
        $badger->setCustomerGroupIds([]);
        $badger->setVisuals([]);
        return $badger;
    }
}
