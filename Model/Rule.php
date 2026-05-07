<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Model\Condition\CombineFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection;
use Magento\Rule\Model\Action\CollectionFactory;

class Rule extends AbstractModel
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        private readonly CombineFactory $conditionCombineFactory,
        private readonly CollectionFactory $actionCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, null, null, $data);
    }

    public function getConditionsInstance(): \Iranimij\Badger\Model\Condition\Combine
    {
        return $this->conditionCombineFactory->create();
    }

    public function getActionsInstance(): Collection
    {
        return $this->actionCollectionFactory->create();
    }

    public function getConditionsFieldSetId(string $formName = ''): string
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }
}
