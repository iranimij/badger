<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Observer;

use Iranimij\Badger\Observer\CatalogAttributeDeleteObserver;
use Iranimij\Badger\Observer\CatalogAttributeSaveObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CatalogAttributeObserverTest extends TestCase
{
    private IndexerRegistry $indexerRegistry;
    private CatalogAttributeSaveObserver $saveObserver;
    private CatalogAttributeDeleteObserver $deleteObserver;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->indexerRegistry = $om->get(IndexerRegistry::class);
        $this->saveObserver = $om->get(CatalogAttributeSaveObserver::class);
        $this->deleteObserver = $om->get(CatalogAttributeDeleteObserver::class);
    }

    private function buildObserver(object $attribute): Observer
    {
        $event = new \Magento\Framework\Event(['attribute' => $attribute]);
        $observer = new Observer();
        $observer->setData('event', $event);
        return $observer;
    }

    private function reindexToValid(): void
    {
        $this->indexerRegistry->get('iranimij_badger_index')->reindexAll();
    }

    public function testSaveObserverInvalidatesIndexer(): void
    {
        $this->reindexToValid();
        $indexer = $this->indexerRegistry->get('iranimij_badger_index');
        $this->assertTrue($indexer->isValid(), 'Indexer should be valid after reindex');

        $attribute = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\DataObject::class)
            ->setData(['attribute_code' => 'price']);

        $this->saveObserver->execute($this->buildObserver($attribute));

        $indexer = $this->indexerRegistry->get('iranimij_badger_index');
        $this->assertTrue(
            !$indexer->isValid() || $indexer->isScheduled(),
            'Indexer should be invalidated or scheduled after attribute save'
        );
    }

    public function testDeleteObserverInvalidatesIndexer(): void
    {
        $this->reindexToValid();
        $indexer = $this->indexerRegistry->get('iranimij_badger_index');
        $this->assertTrue($indexer->isValid(), 'Indexer should be valid after reindex');

        $attribute = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\DataObject::class)
            ->setData(['attribute_code' => 'color']);

        $this->deleteObserver->execute($this->buildObserver($attribute));

        $indexer = $this->indexerRegistry->get('iranimij_badger_index');
        $this->assertTrue(
            !$indexer->isValid() || $indexer->isScheduled(),
            'Indexer should be invalidated or scheduled after attribute delete'
        );
    }

    public function testSaveObserverWithNullAttributeDoesNotThrow(): void
    {
        $event = new \Magento\Framework\Event([]);
        $observer = new Observer();
        $observer->setData('event', $event);

        $this->saveObserver->execute($observer);
        $this->addToAssertionCount(1);
    }

    public function testDeleteObserverWithNullAttributeDoesNotThrow(): void
    {
        $event = new \Magento\Framework\Event([]);
        $observer = new Observer();
        $observer->setData('event', $event);

        $this->deleteObserver->execute($observer);
        $this->addToAssertionCount(1);
    }
}
