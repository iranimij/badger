<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\ReadModel;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\Indexer\BadgerIndexer;
use Iranimij\Badger\Model\ReadModel\BadgerResolver;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class BadgerResolverTest extends TestCase
{
    private BadgerResolver $resolver;
    private BadgerRepositoryInterface $repository;
    private BadgerIndexer $indexer;
    private ProductRepositoryInterface $productRepository;
    private ?string $productSku = null;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->resolver = $om->get(BadgerResolver::class);
        $this->repository = $om->get(BadgerRepositoryInterface::class);
        $this->indexer = $om->get(BadgerIndexer::class);
        $this->productRepository = $om->get(ProductRepositoryInterface::class);

        $sku = 'badger-resolver-test-' . uniqid();
        $this->productSku = $sku;

        /** @var ProductInterfaceFactory $factory */
        $factory = $om->get(ProductInterfaceFactory::class);
        $product = $factory->create();
        $product->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Badger Resolver Test Product')
            ->setSku($sku)
            ->setPrice(100.00)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['qty' => 10, 'is_in_stock' => 1]);
        $this->productRepository->save($product);
    }

    protected function tearDown(): void
    {
        if ($this->productSku !== null) {
            /** @var Registry $registry */
            $registry = Bootstrap::getObjectManager()->get(Registry::class);
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', true);
            try {
                $this->productRepository->deleteById($this->productSku);
            } catch (\Exception $e) {
                // already gone via transaction rollback
            }
            $registry->unregister('isSecureArea');
            $this->productSku = null;
        }
    }

    public function testResolverReturnsEmptyWhenNoBadgers(): void
    {
        $product = $this->productRepository->get($this->productSku);
        $resolved = $this->resolver->resolve($product, Surface::CATEGORY_GRID, 1, 0);

        self::assertIsArray($resolved);
    }

    public function testResolverReturnsBadgerForMatchingProduct(): void
    {
        $product = $this->productRepository->get($this->productSku);
        $product->setSpecialPrice($product->getPrice() * 0.8);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+30 day')));
        $this->productRepository->save($product);

        $badger = $this->makeEnabledBadger(
            'On Sale Resolver Test',
            json_encode([
                'op' => 'AND',
                'children' => [['token' => 'on_sale', 'args' => []]],
            ]),
            Surface::CATEGORY_GRID
        );
        $saved = $this->repository->save($badger);

        $resolved = $this->resolver->resolve($product, Surface::CATEGORY_GRID, 1, 0);

        $ids = array_map(fn ($r) => $r->badgerId, $resolved);
        self::assertContains((int) $saved->getBadgerId(), $ids);
    }

    public function testResolverFiltersDisabledBadgers(): void
    {
        $product = $this->productRepository->get($this->productSku);

        $badger = $this->makeEnabledBadger('Disabled Badge', null, Surface::CATEGORY_GRID);
        $badger->setIsEnabled(false);
        $saved = $this->repository->save($badger);

        $resolved = $this->resolver->resolve($product, Surface::CATEGORY_GRID, 1, 0);

        $ids = array_map(fn ($r) => $r->badgerId, $resolved);
        self::assertNotContains((int) $saved->getBadgerId(), $ids);
    }

    public function testResolverFiltersExpiredBadger(): void
    {
        $product = $this->productRepository->get($this->productSku);

        $badger = $this->makeEnabledBadger('Expired Badge', null, Surface::CATEGORY_GRID);
        $badger->setActiveFrom(date('Y-m-d H:i:s', strtotime('-4 hours')));
        $badger->setActiveTo(date('Y-m-d H:i:s', strtotime('-1 hour')));
        $saved = $this->repository->save($badger);

        $resolved = $this->resolver->resolve($product, Surface::CATEGORY_GRID, 1, 0);

        $ids = array_map(fn ($r) => $r->badgerId, $resolved);
        self::assertNotContains((int) $saved->getBadgerId(), $ids);
    }

    public function testResolverFiltersWrongSurface(): void
    {
        $product = $this->productRepository->get($this->productSku);

        $badger = $this->makeEnabledBadger('PDP Only Badge', null, Surface::PRODUCT_PAGE);
        $saved = $this->repository->save($badger);

        $resolved = $this->resolver->resolve($product, Surface::CATEGORY_GRID, 1, 0);

        $ids = array_map(fn ($r) => $r->badgerId, $resolved);
        self::assertNotContains((int) $saved->getBadgerId(), $ids);
    }

    public function testResolverResolvesPlaceholdersInLabelText(): void
    {
        $product = $this->productRepository->get($this->productSku);

        $badger = $this->makeEnabledBadger('Placeholder Badge', null, Surface::CATEGORY_GRID, 'SKU: {{sku}}');
        $saved = $this->repository->save($badger);

        $resolved = $this->resolver->resolve($product, Surface::CATEGORY_GRID, 1, 0);

        $match = null;
        foreach ($resolved as $r) {
            if ($r->badgerId === (int) $saved->getBadgerId()) {
                $match = $r;
                break;
            }
        }

        self::assertNotNull($match);
        self::assertSame('SKU: ' . $this->productSku, $match->labelText);
    }

    private function makeEnabledBadger(
        string $name,
        ?string $conditions = null,
        Surface $surface = Surface::CATEGORY_GRID,
        string $labelText = 'Test'
    ): BadgerInterface {
        $om = Bootstrap::getObjectManager();
        $badger = $om->create(BadgerInterface::class);
        $badger->setName($name);
        $badger->setIsEnabled(true);
        $badger->setPriority(10);
        $badger->setIsExclusive(false);
        $badger->setUseForParent(false);
        $badger->setStoreIds([0]);
        $badger->setCustomerGroupIds([]);

        if ($conditions !== null) {
            $badger->setConditionsPayload($conditions);
        }

        $visual = $om->create(VisualInterface::class);
        $visual->setSurface($surface->value);
        $visual->setPlacement(Placement::TOP_LEFT->value);
        $visual->setShapeKind(ShapeKind::ROUNDED_RECT->value);
        $visual->setLabelText($labelText);
        $visual->setSizePercent(25);
        $badger->setVisuals([$visual]);

        return $badger;
    }
}
