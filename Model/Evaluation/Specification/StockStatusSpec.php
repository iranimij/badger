<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class StockStatusSpec implements SpecificationInterface
{
    public const TOKEN = 'stock_status';

    public const EXPECTS_IN = 'in_stock';
    public const EXPECTS_OUT = 'out_of_stock';

    public function __construct(
        private readonly StockRegistryInterface $stockRegistry,
        private readonly string $expected
    ) {
        if ($expected !== self::EXPECTS_IN && $expected !== self::EXPECTS_OUT) {
            throw new \InvalidArgumentException("Unsupported stock status: $expected");
        }
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $sku = $ctx->product->getSku();
        if ($sku === null || $sku === '') {
            return false;
        }
        $status = $this->stockRegistry->getStockStatusBySku($sku);
        $isIn = (int) $status->getStockStatus() === 1;
        return $this->expected === self::EXPECTS_IN ? $isIn : !$isIn;
    }
}
