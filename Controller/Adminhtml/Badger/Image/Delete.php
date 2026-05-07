<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger\Image;

use Iranimij\Badger\Controller\Adminhtml\Badger\AbstractAction;
use Iranimij\Badger\Service\Media\BadgerMediaStorage;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Delete extends AbstractAction implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly BadgerMediaStorage $storage
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $path = (string) $this->getRequest()->getParam('path', '');
        if ($path === '') {
            return $result->setData(['success' => false, 'error' => __('Missing path.')]);
        }
        try {
            $deleted = $this->storage->delete($path);
            return $result->setData(['success' => $deleted]);
        } catch (\Throwable $e) {
            return $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
