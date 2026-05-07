<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Cron;

use Iranimij\Badger\Cron\RefreshIsNewCache;
use Magento\Framework\App\Cache\TypeListInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RefreshIsNewCacheTest extends TestCase
{
    public function testInvalidatesCaches(): void
    {
        $cacheList = $this->createMock(TypeListInterface::class);
        $cacheList->expects(self::once())
            ->method('invalidate')
            ->with(['block_html', 'full_page']);

        (new RefreshIsNewCache($cacheList, $this->createMock(LoggerInterface::class)))->execute();
    }

    public function testLogsErrorOnException(): void
    {
        $cacheList = $this->createMock(TypeListInterface::class);
        $cacheList->method('invalidate')->willThrowException(new \RuntimeException('e'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        (new RefreshIsNewCache($cacheList, $logger))->execute();
    }
}
