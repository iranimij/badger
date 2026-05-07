<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Magento\Backend\App\Action;

abstract class AbstractAction extends Action
{
    public const ADMIN_RESOURCE = 'Iranimij_Badger::badger_manage';
}
