<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

class MassDisable extends AbstractMassAction
{
    protected function applyTo(int $badgerId): void
    {
        $badger = $this->repository->getById($badgerId);
        $badger->setIsEnabled(false);
        $this->repository->save($badger);
    }

    protected function successMessage(int $count): \Magento\Framework\Phrase
    {
        return __('%1 badger(s) disabled.', $count);
    }
}
