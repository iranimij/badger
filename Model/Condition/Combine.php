<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Condition;

use Iranimij\Badger\Model\Condition\IsNew;
use Iranimij\Badger\Model\Condition\OnSale;
use Iranimij\Badger\Model\Condition\Qty;
use Iranimij\Badger\Model\Condition\StockStatus;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine as MagentoCombine;
use Magento\Rule\Model\Condition\Context;

class Combine extends MagentoCombine
{
    public function __construct(
        Context $context,
        private readonly ProductFactory $productConditionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(Combine::class);
    }

    public function getNewChildSelectOptions(): array
    {
        $conditions = AbstractCondition::getNewChildSelectOptions();
        $conditions = array_merge($conditions, [
            [
                'label' => __('Conditions Combination'),
                'value' => Combine::class,
            ],
            [
                'label' => __('Custom Conditions'),
                'value' => [
                    ['value' => OnSale::class,     'label' => __('Product is on sale')],
                    ['value' => IsNew::class,       'label' => __('Product is new')],
                    ['value' => StockStatus::class, 'label' => __('Stock status')],
                    ['value' => Qty::class,         'label' => __('Quantity in stock')],
                ],
            ],
            [
                'label' => __('Product Attribute'),
                'value' => $this->getProductAttributeOptions(),
            ],
        ]);

        return $conditions;
    }

    private function getProductAttributeOptions(): array
    {
        $productAttributes = $this->productConditionFactory->create()
            ->loadAttributeOptions()
            ->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        return $attributes;
    }
}
