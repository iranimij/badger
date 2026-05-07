<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Cron;

use Iranimij\Badger\Cron\DeactivateExpired;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeactivateExpiredTest extends TestCase
{
    public function testFlipsBadgersPastTo(): void
    {
        $now = '2026-04-24 00:00:00';

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->expects(self::once())
            ->method('update')
            ->with(
                'iranimij_badger_entity',
                ['is_enabled' => 0],
                self::callback(function (array $where) use ($now): bool {
                    return $where['is_enabled = ?'] === 1
                        && $where['active_to <= ?'] === $now;
                })
            );

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($adapter);
        $resource->method('getTableName')->willReturnArgument(0);

        $dateTime = $this->createMock(DateTime::class);
        $dateTime->method('gmtDate')->willReturn($now);

        (new DeactivateExpired($resource, $dateTime, $this->createMock(LoggerInterface::class)))->execute();
    }

    public function testLogsErrorOnException(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('update')->willThrowException(new \RuntimeException('x'));

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($adapter);
        $resource->method('getTableName')->willReturnArgument(0);

        $dateTime = $this->createMock(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-04-24 00:00:00');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        (new DeactivateExpired($resource, $dateTime, $logger))->execute();
    }
}
