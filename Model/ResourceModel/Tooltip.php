<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tooltip extends AbstractDb
{
    public const MAIN_TABLE = 'iranimij_badger_tooltip';

    protected function _construct(): void
    {
        $this->_init(self::MAIN_TABLE, 'badger_id');
    }

    public function loadByBadger(int $badgerId): ?array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('badger_id = ?', $badgerId)
            ->limit(1);
        $row = $connection->fetchRow($select);
        return $row === false ? null : $row;
    }

    public function saveRow(array $row): void
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $row);
    }

    public function deleteForBadger(int $badgerId): void
    {
        $this->getConnection()->delete($this->getMainTable(), ['badger_id = ?' => $badgerId]);
    }
}
