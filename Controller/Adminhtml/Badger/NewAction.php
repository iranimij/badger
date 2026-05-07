<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends AbstractAction implements HttpGetActionInterface
{
    public function execute(): ResultInterface
    {
        $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $forward->forward('edit');
        return $forward;
    }
}
