<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Magento\Catalog\Api\Data\ProductInterface;

final class PlaceholderContext
{
    public function __construct(
        public readonly ProductInterface $product,
        public readonly ?BadgerInterface $badger = null,
        public readonly int $storeId = 0,
        public readonly int $customerGroupId = 0,
        public readonly float $qty = 1.0
    ) {
    }
}
