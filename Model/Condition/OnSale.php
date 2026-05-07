<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class OnSale extends AbstractCondition
{
    public function __construct(
        Context $context,
        private readonly Yesno $yesno,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('Product is on sale');
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
        $collection->addPriceData();
    }

    public function validate(AbstractModel $model): bool
    {
        $finalPrice   = (float) ($model->getData('final_price') ?? $model->getFinalPrice());
        $regularPrice = (float) ($model->getData('price')       ?? $model->getPrice());
        $isOnSale     = $regularPrice > 0.001 && $finalPrice < ($regularPrice - 0.001);

        return $this->validateAttribute((int) $isOnSale);
    }
}
