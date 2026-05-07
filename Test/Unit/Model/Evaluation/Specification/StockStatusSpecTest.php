<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\StockStatusSpec;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use PHPUnit\Framework\TestCase;

class StockStatusSpecTest extends TestCase
{
    private function ctxForSku(string $sku): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getSku')->willReturn($sku);
        return new EvaluationContext($product, 1, 0);
    }

    private function stockRegistry(int $status): StockRegistryInterface
    {
        $stock = $this->createMock(StockStatusInterface::class);
        $stock->method('getStockStatus')->willReturn($status);
        $registry = $this->createMock(StockRegistryInterface::class);
        $registry->method('getStockStatusBySku')->willReturn($stock);
        return $registry;
    }

    public function testInStockExpectation(): void
    {
        $spec = new StockStatusSpec($this->stockRegistry(1), StockStatusSpec::EXPECTS_IN);
        self::assertTrue($spec->isSatisfiedBy($this->ctxForSku('SKU1')));
    }

    public function testOutOfStockExpectation(): void
    {
        $spec = new StockStatusSpec($this->stockRegistry(0), StockStatusSpec::EXPECTS_OUT);
        self::assertTrue($spec->isSatisfiedBy($this->ctxForSku('SKU1')));
    }

    public function testMismatchReturnsFalse(): void
    {
        $spec = new StockStatusSpec($this->stockRegistry(0), StockStatusSpec::EXPECTS_IN);
        self::assertFalse($spec->isSatisfiedBy($this->ctxForSku('SKU1')));
    }

    public function testEmptySkuFalse(): void
    {
        $spec = new StockStatusSpec($this->stockRegistry(1), StockStatusSpec::EXPECTS_IN);
        self::assertFalse($spec->isSatisfiedBy($this->ctxForSku('')));
    }

    public function testRejectsUnknownExpectation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StockStatusSpec($this->stockRegistry(1), 'maybe');
    }
}
