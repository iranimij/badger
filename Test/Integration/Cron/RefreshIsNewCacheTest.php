<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Cron;

use Iranimij\Badger\Cron\RefreshIsNewCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RefreshIsNewCacheTest extends TestCase
{
    private RefreshIsNewCache $cron;
    private TypeListInterface $cacheTypeList;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->cron = $om->get(RefreshIsNewCache::class);
        $this->cacheTypeList = $om->get(TypeListInterface::class);
    }

    public function testExecuteDoesNotThrow(): void
    {
        $this->cron->execute();
        $this->addToAssertionCount(1);
    }

    public function testExecuteInvalidatesBlockHtmlAndFullPageCache(): void
    {
        // Clean first so we have a known state
        $this->cacheTypeList->cleanType('block_html');
        $this->cacheTypeList->cleanType('full_page');

        $this->cron->execute();

        $invalidated = $this->cacheTypeList->getInvalidated();
        $this->assertArrayHasKey('block_html', $invalidated, 'block_html cache should be invalidated');
        $this->assertArrayHasKey('full_page', $invalidated, 'full_page cache should be invalidated');
    }

    public function testExecuteIsIdempotent(): void
    {
        $this->cron->execute();
        $this->cron->execute();
        $this->addToAssertionCount(1);
    }
}
