<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Badger extends AbstractDb
{
    public const MAIN_TABLE = 'iranimij_badger_entity';
    public const PRIMARY = 'badger_id';

    protected function _construct(): void
    {
        $this->_init(self::MAIN_TABLE, self::PRIMARY);
    }
}
