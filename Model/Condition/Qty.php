<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class Qty extends AbstractCondition
{
    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('Quantity in stock');
    }

    public function getInputType(): string
    {
        return 'numeric';
    }

    public function getValueElementType(): string
    {
        return 'text';
    }

    public function collectValidatedAttributes(ProductCollection $collection): void
    {
        $select    = $collection->getSelect();
        $fromParts = $select->getPart(\Zend_Db_Select::FROM);
        if (!isset($fromParts['cataloginventory_stock_item'])) {
            $table = $this->resource->getTableName('cataloginventory_stock_item');
            $select->joinLeft(
                ['cataloginventory_stock_item' => $table],
                'cataloginventory_stock_item.product_id = e.entity_id'
                . ' AND cataloginventory_stock_item.website_id = 0',
                ['qty' => 'cataloginventory_stock_item.qty']
            );
        }
    }

    public function validate(AbstractModel $model): bool
    {
        $qty = (float) ($model->getData('qty') ?? 0);
        return $this->validateAttribute($qty);
    }
}
