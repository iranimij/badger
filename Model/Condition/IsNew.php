<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class IsNew extends AbstractCondition
{
    public function __construct(
        Context $context,
        private readonly Yesno $yesno,
        private readonly TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('Product is new');
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
        $collection->addAttributeToSelect('news_from_date');
        $collection->addAttributeToSelect('news_to_date');
    }

    public function validate(AbstractModel $model): bool
    {
        $now      = $this->timezone->date();
        $fromRaw  = $model->getNewsFromDate();
        $toRaw    = $model->getNewsToDate();
        $isNew    = false;

        if ($fromRaw || $toRaw) {
            $fromDate = $fromRaw ? $this->timezone->date(new \DateTime($fromRaw)) : null;
            $toDate   = $toRaw  ? $this->timezone->date(new \DateTime($toRaw))   : null;
            $isNew    = (!$fromDate || $now >= $fromDate) && (!$toDate || $now <= $toDate);
        }

        return $this->validateAttribute((int) $isNew);
    }
}
