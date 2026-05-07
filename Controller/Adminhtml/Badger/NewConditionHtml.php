<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Model\RuleFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\ConditionInterface;

class NewConditionHtml extends AbstractAction implements HttpPostActionInterface, HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): void
    {
        $objectId     = $this->getRequest()->getParam('id');
        $formNamespace = $this->getRequest()->getParam('form_namespace');
        $types        = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type', '')));
        $objectType   = $types[0];
        $responseBody = '';

        if (class_exists($objectType) && !in_array(ConditionInterface::class, class_implements($objectType) ?: [])) {
            $this->getResponse()->setBody($responseBody);
            return;
        }

        /** @var AbstractCondition $conditionModel */
        $conditionModel = $this->_objectManager->create($objectType)
            ->setId($objectId)
            ->setType($objectType)
            ->setRule($this->ruleFactory->create())
            ->setPrefix('conditions');

        if (!empty($types[1])) {
            $conditionModel->setAttribute($types[1]);
        }

        if ($conditionModel instanceof AbstractCondition) {
            $conditionModel->setJsFormObject($this->getRequest()->getParam('form'));
            $conditionModel->setFormName($formNamespace);
            $responseBody = $conditionModel->asHtmlRecursive();
        }

        $this->getResponse()->setBody($responseBody);
    }
}
