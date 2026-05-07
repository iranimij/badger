<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel\Mview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\ViewInterface;

class ChangelogCleaner
{
    private const VIEW_ID = 'iranimij_badger_index';
    private const CHANGELOG_TABLE = 'iranimij_badger_index_cl';

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly ViewInterface $view
    ) {
    }

    public function clean(): int
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::CHANGELOG_TABLE);
        if (!$connection->isTableExists($table)) {
            return 0;
        }
        $this->view->load(self::VIEW_ID);
        $version = (int) $this->view->getState()->getVersionId();
        if ($version <= 0) {
            return 0;
        }
        return (int) $connection->delete($table, ['version_id <= ?' => $version]);
    }
}
