<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Service;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\BadgerHydrator;
use Iranimij\Badger\Service\GetMatchedProductIds;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class GetMatchedProductIdsTest extends TestCase
{
    private GetMatchedProductIds $service;
    private BadgerRepositoryInterface $repository;
    private ProductRepositoryInterface $productRepository;
    private BadgerHydrator $hydrator;
    private array $createdSkus = [];

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->service = $om->get(GetMatchedProductIds::class);
        $this->repository = $om->get(BadgerRepositoryInterface::class);
        $this->hydrator = $om->get(BadgerHydrator::class);
        $this->productRepository = $om->get(ProductRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        foreach ($this->createdSkus as $sku) {
            try {
                $this->productRepository->deleteById($sku);
            } catch (\Exception) {
            }
        }
        $registry->unregister('isSecureArea');
    }

    private function createProduct(string $sku, float $price, float $specialPrice = 0.0): void
    {
        $om = Bootstrap::getObjectManager();
        $factory = $om->get(\Magento\Catalog\Api\Data\ProductInterfaceFactory::class);
        $product = $factory->create();
        $product->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Product ' . $sku)
            ->setSku($sku)
            ->setPrice($price)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['qty' => 10, 'is_in_stock' => 1]);
        if ($specialPrice > 0) {
            $product->setSpecialPrice($specialPrice);
        }
        $this->productRepository->save($product);
        $this->createdSkus[] = $sku;
    }

    private function createBadger(array $extra = []): BadgerInterface
    {
        $data = array_merge([
            BadgerInterface::NAME => 'Test Matched ' . uniqid(),
            BadgerInterface::IS_ENABLED => true,
            BadgerInterface::PRIORITY => 10,
            BadgerInterface::IS_EXCLUSIVE => false,
        ], $extra);
        return $this->repository->save($this->hydrator->fromArray($data));
    }

    public function testNoConditionsMatchesAllProducts(): void
    {
        $sku = 'gmp-no-cond-' . uniqid();
        $this->createProduct($sku, 50.0);

        $badger = $this->createBadger();
        $ids = $this->service->execute((int) $badger->getBadgerId());

        $this->assertIsArray($ids);
        $this->assertNotEmpty($ids, 'No-condition badger should match at least one product');
        $product = $this->productRepository->get($sku);
        $this->assertContains((int) $product->getId(), $ids);
    }

    public function testThrowsForNonExistentBadger(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->service->execute(999999);
    }

    public function testEmptyConditionsPayloadMatchesAll(): void
    {
        $sku = 'gmp-empty-cond-' . uniqid();
        $this->createProduct($sku, 50.0);

        $badger = $this->createBadger([BadgerInterface::CONDITIONS_PAYLOAD => '']);
        $ids = $this->service->execute((int) $badger->getBadgerId());

        $this->assertIsArray($ids);
        $product = $this->productRepository->get($sku);
        $this->assertContains((int) $product->getId(), $ids);
    }

    public function testReturnedIdsAreIntegers(): void
    {
        $badger = $this->createBadger();
        $ids = $this->service->execute((int) $badger->getBadgerId());

        foreach ($ids as $id) {
            $this->assertIsInt($id);
        }
    }
}
