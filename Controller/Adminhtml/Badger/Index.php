<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Index extends AbstractAction implements HttpGetActionInterface
{
    public function execute(): ResultInterface
    {
        /** @var Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->setActiveMenu('Iranimij_Badger::menu_manage');
        $result->getConfig()->getTitle()->prepend(__('Badgers'));
        return $result;
    }
}
