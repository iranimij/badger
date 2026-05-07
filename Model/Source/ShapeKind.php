<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Iranimij\Badger\Model\Enum\ShapeKind as ShapeKindEnum;
use Magento\Framework\Data\OptionSourceInterface;

class ShapeKind implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];
        foreach (ShapeKindEnum::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => __(ucwords(strtolower(str_replace('_', ' ', $case->name))))];
        }
        return $options;
    }
}
