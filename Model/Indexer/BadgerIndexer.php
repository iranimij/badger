<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class BadgerIndexer implements ActionInterface, MviewActionInterface
{
    public function __construct(
        private readonly IndexBuilder $indexBuilder,
        private readonly CacheContext $cacheContext
    ) {
    }

    public function executeFull(): void
    {
        $this->indexBuilder->rebuildAll();
    }

    /**
     * @param int[] $ids
     */
    public function executeList(array $ids): void
    {
        $this->execute($ids);
    }

    /**
     * @param int $id
     */
    public function executeRow($id): void
    {
        $this->execute([(int) $id]);
    }

    /**
     * @param int[] $ids
     */
    public function execute($ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return;
        }
        $this->indexBuilder->rebuildForProducts($ids);
        $this->cacheContext->registerProducts($ids);
    }
}
