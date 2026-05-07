<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class StockStatus extends AbstractCondition
{
    public function __construct(
        Context $context,
        private readonly Yesno $yesno,
        private readonly ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('In stock');
    }

    public function getInputType(): string
    {
        return 'select';
    }

    public function getValueElementType(): string
    {
        return 'select';
    }

    public function getValueSelectOptions(): array
    {
        return $this->yesno->toOptionArray();
    }

    public function collectValidatedAttributes(ProductCollection $collection): void
    {
        $select    = $collection->getSelect();
        $fromParts = $select->getPart(\Zend_Db_Select::FROM);
        if (!isset($fromParts['stock_status_index'])) {
            $table = $this->resource->getTableName('cataloginventory_stock_status');
            $select->joinLeft(
                ['stock_status_index' => $table],
                'stock_status_index.product_id = e.entity_id AND stock_status_index.website_id = 0',
                ['stock_status' => 'stock_status_index.stock_status']
            );
        }
    }

    public function validate(AbstractModel $model): bool
    {
        $status = (int) ($model->getData('is_salable') ?? $model->getData('stock_status') ?? 0);
        return $this->validateAttribute($status);
    }
}
