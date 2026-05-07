<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel\Badger;

use Iranimij\Badger\Model\Badger as BadgerModel;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = BadgerResource::PRIMARY;

    protected function _construct(): void
    {
        $this->_init(BadgerModel::class, BadgerResource::class);
    }
}
