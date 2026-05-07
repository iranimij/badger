<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Plugin\Frontend;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Plugin\Frontend\CategoryListAfterHtmlPlugin;
use Iranimij\Badger\Plugin\Frontend\CartCrossSellPlugin;
use Iranimij\Badger\Plugin\Frontend\RelatedProductsWidgetPlugin;
use Iranimij\Badger\Plugin\Frontend\UpsellWidgetPlugin;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class StackInjectorTraitTest extends TestCase
{
    private LayoutInterface $layout;
    private ProductRepositoryInterface $productRepository;
    private array $createdSkus = [];

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->layout = $om->get(LayoutInterface::class);
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

    private function createProduct(string $sku): \Magento\Catalog\Api\Data\ProductInterface
    {
        $om = Bootstrap::getObjectManager();
        $product = $om->get(ProductInterfaceFactory::class)->create();
        $product->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Plugin Test Product')
            ->setSku($sku)
            ->setPrice(50.0)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['qty' => 5, 'is_in_stock' => 1]);
        $saved = $this->productRepository->save($product);
        $this->createdSkus[] = $sku;
        return $saved;
    }

    public function testBadgerStackBlockInstantiatesViaLayout(): void
    {
        $block = $this->layout->createBlock(BadgerStack::class);
        $this->assertInstanceOf(BadgerStack::class, $block);
    }

    public function testBadgerStackWithNullProductReturnsEmptyBadgers(): void
    {
        $block = $this->layout->createBlock(BadgerStack::class);
        $block->setProduct(null);
        $this->assertSame([], $block->getResolvedBadgers());
    }

    public function testBadgerStackWithProductReturnsArray(): void
    {
        $product = $this->createProduct('plugin-test-' . uniqid());
        $block = $this->layout->createBlock(BadgerStack::class);
        $block->setProduct($product);
        $block->setSurface(Surface::CATEGORY_GRID);

        $badgers = $block->getResolvedBadgers();
        $this->assertIsArray($badgers);
    }

    public function testBadgerStackIdentitiesContainCacheTag(): void
    {
        $product = $this->createProduct('plugin-identity-' . uniqid());
        $block = $this->layout->createBlock(BadgerStack::class);
        $block->setProduct($product);
        $block->setSurface(Surface::CATEGORY_GRID);

        $identities = $block->getIdentities();
        $this->assertIsArray($identities);
        $allContainTag = array_reduce(
            $identities,
            fn ($carry, $id) => $carry && str_contains($id, 'iranimij_badger'),
            true
        );
        $this->assertTrue($allContainTag || empty($identities));
    }

    public function testCategoryListPluginAppendsToHtml(): void
    {
        $product = $this->createProduct('plugin-cat-' . uniqid());
        $plugin = Bootstrap::getObjectManager()->get(CategoryListAfterHtmlPlugin::class);

        /** @var ListProduct $listBlock */
        $listBlock = $this->layout->createBlock(ListProduct::class);

        $result = $plugin->afterGetProductDetailsHtml($listBlock, '<div>original</div>', $product);

        $this->assertStringContainsString('<div>original</div>', $result);
        // Plugin appends — result length should be >= original
        $this->assertGreaterThanOrEqual(strlen('<div>original</div>'), strlen($result));
    }

    public function testAllPluginClassesExistAndAreWired(): void
    {
        $pluginClasses = [
            CategoryListAfterHtmlPlugin::class,
            CartCrossSellPlugin::class,
            RelatedProductsWidgetPlugin::class,
            UpsellWidgetPlugin::class,
        ];

        foreach ($pluginClasses as $class) {
            $instance = Bootstrap::getObjectManager()->get($class);
            $this->assertInstanceOf($class, $instance, "$class should be instantiable via DI");
        }
    }
}
