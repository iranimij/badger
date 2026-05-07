<?php
declare(strict_types=1);

namespace Iranimij\Badger\Observer;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;

class CatalogAttributeSaveObserver implements ObserverInterface
{
    private const INDEXER_ID = 'iranimij_badger_index';

    public function __construct(
        private readonly IndexerRegistry $indexerRegistry
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var EavAttributeInterface $attribute */
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute === null) {
            return;
        }

        $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->invalidate();
        }
    }
}
