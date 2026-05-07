<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger\Image;

use Iranimij\Badger\Controller\Adminhtml\Badger\AbstractAction;
use Iranimij\Badger\Service\Media\BadgerMediaStorage;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Upload extends AbstractAction implements HttpPostActionInterface
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
        try {
            if (empty($_FILES['image']['tmp_name'])) {
                return $result->setData(['error' => __('No file provided.'), 'errorcode' => 1]);
            }
            $uploaded = $this->storage->upload('image');
            return $result->setData([
                'file' => $uploaded['file'],
                'path' => $uploaded['path'],
                'url'  => $this->_url->getBaseUrl(['_type' => 'media']) . $uploaded['path'],
            ]);
        } catch (\Throwable $e) {
            return $result->setData(['error' => $e->getMessage(), 'errorcode' => 2]);
        }
    }
}
