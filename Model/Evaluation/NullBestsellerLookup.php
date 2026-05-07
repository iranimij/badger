<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation;

class NullBestsellerLookup implements BestsellerLookupInterface
{
    public function isBestseller(int $productId, int $storeId, int $topN): bool
    {
        return false;
    }
}
