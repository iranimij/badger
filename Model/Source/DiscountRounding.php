<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DiscountRounding implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'round', 'label' => __('Round (nearest)')],
            ['value' => 'floor', 'label' => __('Floor (round down)')],
            ['value' => 'ceil',  'label' => __('Ceiling (round up)')],
        ];
    }
}
