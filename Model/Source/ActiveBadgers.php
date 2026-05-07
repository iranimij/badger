<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\ResourceConnection;

class ActiveBadgers implements OptionSourceInterface
{
    public function __construct(private readonly ResourceConnection $resource) {}

    public function toOptionArray(): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('iranimij_badger_entity');

        $rows = $connection->fetchAll(
            $connection->select()
                ->from($table, ['badger_id', 'name'])
                ->where('is_enabled = ?', 1)
                ->order('name ASC')
        );

        $options = [['value' => '', 'label' => __('-- None --')]];
        foreach ($rows as $row) {
            $options[] = ['value' => (int)$row['badger_id'], 'label' => $row['name']];
        }

        return $options;
    }
}
