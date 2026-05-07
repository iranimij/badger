<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Iranimij\Badger\Model\Enum\Surface as SurfaceEnum;
use Magento\Framework\Data\OptionSourceInterface;

class Surface implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];
        foreach (SurfaceEnum::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => __(ucwords(strtolower(str_replace('_', ' ', $case->name))))];
        }
        return $options;
    }
}
