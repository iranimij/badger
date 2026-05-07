<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

class MassDelete extends AbstractMassAction
{
    protected function applyTo(int $badgerId): void
    {
        $this->repository->deleteById($badgerId);
    }

    protected function successMessage(int $count): \Magento\Framework\Phrase
    {
        return __('%1 badger(s) deleted.', $count);
    }
}
