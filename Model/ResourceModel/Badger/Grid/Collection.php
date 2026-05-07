<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel\Badger\Grid;

use Iranimij\Badger\Model\Badger as BadgerModel;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult implements SearchResultInterface
{
    protected function _construct(): void
    {
        $this->_init(BadgerModel::class, BadgerResource::class);
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setItems(array $items = null)
    {
        return $this;
    }
}
