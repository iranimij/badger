<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class CustomerGroupLink
{
    public const MAIN_TABLE = 'iranimij_badger_customer_group';

    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    public function sync(int $badgerId, array $groupIds): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::MAIN_TABLE);
        $connection->delete($table, ['badger_id = ?' => $badgerId]);
        $rows = [];
        foreach (array_unique(array_map('intval', $groupIds)) as $groupId) {
            $rows[] = ['badger_id' => $badgerId, 'customer_group_id' => $groupId];
        }
        if ($rows !== []) {
            $connection->insertMultiple($table, $rows);
        }
    }

    public function fetch(int $badgerId): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::MAIN_TABLE);
        $select = $connection->select()
            ->from($table, ['customer_group_id'])
            ->where('badger_id = ?', $badgerId);
        return array_map('intval', $connection->fetchCol($select));
    }
}
