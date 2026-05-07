<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\DataProvider\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class GeneralModifier implements ModifierInterface
{
    public function modifyData(array $data): array
    {
        foreach ($data as $id => $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['is_enabled'] = !empty($row['is_enabled']) ? '1' : '0';
            $row['is_exclusive'] = !empty($row['is_exclusive']) ? '1' : '0';
            $row['use_for_parent'] = !empty($row['use_for_parent']) ? '1' : '0';
            $data[$id] = $row;
        }
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
