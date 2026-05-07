<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CompositeOps implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'AND', 'label' => __('All conditions must match (AND)')->render()],
            ['value' => 'OR', 'label' => __('Any condition matches (OR)')->render()],
        ];
    }
}
