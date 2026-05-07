<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Delete extends AbstractAction implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly BadgerRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = (int) $this->getRequest()->getParam('badger_id');
        if ($id <= 0) {
            $this->messageManager->addErrorMessage(__('Badger not found.'));
            return $redirect->setPath('*/*/');
        }

        try {
            $this->repository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('Badger deleted.'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $redirect->setPath('*/*/');
    }
}
