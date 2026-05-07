<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\ResourceModel\Badger\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractMassAction extends AbstractAction implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        protected readonly Filter $filter,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly BadgerRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count = 0;
        foreach ($collection->getItems() as $item) {
            $id = (int) $item->getData(BadgerInterface::BADGER_ID);
            if ($id <= 0) {
                continue;
            }
            try {
                $this->applyTo($id);
                $count++;
            } catch (\Throwable) {
                // skip per-item failures
            }
        }
        $this->messageManager->addSuccessMessage($this->successMessage($count));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }

    abstract protected function applyTo(int $badgerId): void;

    protected function successMessage(int $count): \Magento\Framework\Phrase
    {
        return __('%1 badger(s) updated.', $count);
    }
}
