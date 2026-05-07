<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

class Edit extends AbstractAction implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly BadgerRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('badger_id');
        if ($id > 0) {
            try {
                $this->repository->getById($id);
            } catch (NoSuchEntityException) {
                $this->messageManager->addErrorMessage(__('This badger no longer exists.'));
                $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $redirect->setPath('*/*/');
            }
        }

        /** @var Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->setActiveMenu('Iranimij_Badger::menu_manage');
        $title = $id > 0 ? __('Edit Badger') : __('New Badger');
        $result->getConfig()->getTitle()->prepend($title);
        return $result;
    }
}
