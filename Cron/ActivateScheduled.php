<?php
declare(strict_types=1);

namespace Iranimij\Badger\Cron;

use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class ActivateScheduled
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(BadgerResource::MAIN_TABLE);
        $now = $this->dateTime->gmtDate();
        try {
            $connection->update(
                $table,
                ['is_enabled' => 1],
                [
                    'is_enabled = ?' => 0,
                    'active_from IS NOT NULL',
                    'active_from <= ?' => $now,
                    '(active_to IS NULL OR active_to > ?)' => $now,
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->error('iranimij_badger_activate failed: ' . $e->getMessage());
        }
    }
}
