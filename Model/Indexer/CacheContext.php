<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Indexer;

use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\CacheContext as FrameworkCacheContext;

class CacheContext
{
    public function __construct(
        private readonly FrameworkCacheContext $cacheContext,
        private readonly EventManager $eventManager
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function registerProducts(array $productIds): void
    {
        if ($productIds === []) {
            return;
        }
        $this->cacheContext->registerEntities('catalog_product', $productIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }

    /**
     * @param int[] $badgerIds
     */
    public function registerBadgers(array $badgerIds): void
    {
        if ($badgerIds === []) {
            return;
        }
        $this->cacheContext->registerEntities('iranimij_badger', $badgerIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
