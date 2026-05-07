<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger;

use Magento\Framework\Phrase;

class MassDuplicate extends AbstractMassAction
{
    protected function applyTo(int $badgerId): void
    {
        $this->repository->duplicate($badgerId);
    }

    protected function successMessage(int $count): Phrase
    {
        return __('%1 badger(s) duplicated.', $count);
    }
}
