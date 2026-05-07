<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Visual extends AbstractDb
{
    public const MAIN_TABLE = 'iranimij_badger_visual';

    protected function _construct(): void
    {
        $this->_init(self::MAIN_TABLE, 'badger_id');
    }

    public function deleteForBadger(int $badgerId): void
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), ['badger_id = ?' => $badgerId]);
    }

    public function fetchForBadger(int $badgerId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('badger_id = ?', $badgerId);
        return $connection->fetchAll($select);
    }

    public function saveRow(array $row): void
    {
        $connection = $this->getConnection();
        $connection->insertOnDuplicate($this->getMainTable(), $row);
    }
}
