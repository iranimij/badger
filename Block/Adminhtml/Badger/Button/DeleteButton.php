<?php
declare(strict_types=1);

namespace Iranimij\Badger\Block\Adminhtml\Badger\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        $id = $this->getBadgerId();
        if ($id === null) {
            return [];
        }
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this badger?'),
                $this->getUrl('*/*/delete', ['badger_id' => $id])
            ),
            'sort_order' => 20,
        ];
    }
}
