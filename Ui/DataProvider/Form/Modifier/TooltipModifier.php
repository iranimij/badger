<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Api\Data\TooltipInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class TooltipModifier implements ModifierInterface
{
    public function modifyData(array $data): array
    {
        foreach ($data as $id => $row) {
            if (!is_array($row)) {
                continue;
            }
            $tooltip = $row['tooltip'] ?? null;
            if (!is_array($tooltip)) {
                $row['tooltip'] = [
                    TooltipInterface::IS_ENABLED => false,
                    TooltipInterface::BODY_TEXT => null,
                    TooltipInterface::BACKGROUND_COLOR => null,
                    TooltipInterface::TEXT_COLOR => null,
                ];
            } else {
                $row['tooltip'][TooltipInterface::IS_ENABLED] =
                    !empty($tooltip[TooltipInterface::IS_ENABLED]) ? '1' : '0';
            }
            $data[$id] = $row;
        }
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
