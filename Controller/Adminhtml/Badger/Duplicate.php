<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Duplicate extends AbstractAction implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly BadgerRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('id');
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid badger ID.'));
            return $redirect->setPath('*/*/');
        }
        try {
            $copy = $this->repository->duplicate($id);
            $this->messageManager->addSuccessMessage(__('Badger duplicated (ID: %1).', $copy->getBadgerId()));
            return $redirect->setPath('*/*/edit', ['id' => $copy->getBadgerId()]);
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Could not duplicate badger: %1', $e->getMessage()));
            return $redirect->setPath('*/*/');
        }
    }
}
