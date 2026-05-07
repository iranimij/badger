<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LabelAlignment implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'left', 'label' => __('Left')],
            ['value' => 'center', 'label' => __('Center')],
            ['value' => 'right', 'label' => __('Right')],
        ];
    }
}
