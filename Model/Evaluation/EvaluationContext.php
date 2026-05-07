<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation;

use Magento\Catalog\Api\Data\ProductInterface;

final class EvaluationContext
{
    public function __construct(
        public readonly ProductInterface $product,
        public readonly int $storeId,
        public readonly int $customerGroupId,
        public readonly float $qty = 1.0
    ) {
    }
}
