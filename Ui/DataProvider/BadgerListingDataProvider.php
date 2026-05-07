<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\DataProvider;

use Iranimij\Badger\Model\ResourceModel\Badger\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class BadgerListingDataProvider extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        $items = [];
        foreach ($this->getCollection()->getItems() as $item) {
            $items[] = $item->getData();
        }
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $items,
        ];
    }
}
