<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Service\Placeholder;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\TemplateRenderer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class PlaceholderRenderTest extends TestCase
{
    private TemplateRenderer $renderer;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->renderer = $om->get(TemplateRenderer::class);
    }

    private function makeProduct(string $sku = 'test-sku', float $price = 100.0): ProductInterface
    {
        $product = Bootstrap::getObjectManager()->create(ProductInterface::class);
        $product->setSku($sku);
        $product->setPrice($price);
        return $product;
    }

    public function testSkuPlaceholder(): void
    {
        $product = $this->makeProduct('badger-test-simple');
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('SKU: {{sku}}', $ctx);

        self::assertSame('SKU: badger-test-simple', $result);
    }

    public function testPricePlaceholder(): void
    {
        $product = $this->makeProduct('test-price', 99.99);
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('Price: {{price}}', $ctx);

        self::assertStringContainsString('Price: ', $result);
        self::assertNotSame('Price: {{price}}', $result, 'Placeholder should be resolved');
    }

    public function testDiscountPercentPlaceholder(): void
    {
        $product = $this->makeProduct('test-discount', 100.0);
        $product->setSpecialPrice(75.0);
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('Save {{discount_percent}}%', $ctx);

        self::assertStringContainsString('Save ', $result);
        self::assertStringContainsString('%', $result);
    }

    public function testUnknownPlaceholderPassesThrough(): void
    {
        $product = $this->makeProduct();
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('{{totally_unknown_token}}', $ctx);

        self::assertSame('{{totally_unknown_token}}', $result);
    }

    public function testMultiplePlaceholders(): void
    {
        $product = $this->makeProduct('my-sku');
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('{{sku}} - {{sku}}', $ctx);

        self::assertSame('my-sku - my-sku', $result);
    }

    public function testTemplateWithNoPlaceholders(): void
    {
        $product = $this->makeProduct();
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('Plain text label', $ctx);

        self::assertSame('Plain text label', $result);
    }

    public function testProductAttributePlaceholder(): void
    {
        $product = $this->makeProduct();
        $product->setData('name', 'Badger Test Product');
        $ctx = new PlaceholderContext($product, null, 1, 0);

        $result = $this->renderer->render('{{attr:name}}', $ctx);

        self::assertSame('Badger Test Product', $result);
    }
}
