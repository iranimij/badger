<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\DataProvider\Form\Modifier;

use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class VisualModifier implements ModifierInterface
{
    public function __construct(
        private readonly JsonSerializer $jsonSerializer,
        private readonly UrlInterface $urlBuilder,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function modifyData(array $data): array
    {
        foreach ($data as $id => $row) {
            if (!is_array($row) || !isset($row['visuals']['visuals_container']) || !is_array($row['visuals']['visuals_container'])) {
                continue;
            }
            $visuals = $row['visuals']['visuals_container'];
            foreach ($visuals as $idx => $visual) {
                if (!is_array($visual)) {
                    continue;
                }
                $style = $visual[VisualInterface::STYLE_PAYLOAD] ?? null;
                $styleData = [];
                if (is_string($style) && $style !== '') {
                    try {
                        $decoded = $this->jsonSerializer->decode($style);
                        $styleData = is_array($decoded) ? $decoded : [];
                    } catch (\Throwable) {
                    }
                }
                $visual['bg_color']   = $styleData['bg_color']   ?? '';
                $visual['text_color'] = $styleData['text_color'] ?? '';
                $visuals[$idx] = $visual;
            }
            $row['visuals']['visuals_container'] = $visuals;
            $data[$id] = $row;
        }
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        $uploadUrl       = $this->urlBuilder->getUrl('badger/badger_image/upload');
        $predefinedUrl   = $this->urlBuilder->getUrl('badger/badger_image/getPredefined');
        $mediaBaseUrl    = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        $imageCfgPath = 'visual/children/visuals_container/children/record/children/image_path/arguments/data/config';
        $meta = $this->setNestedValue($meta, $imageCfgPath . '/uploadUrl', $uploadUrl);
        $meta = $this->setNestedValue($meta, $imageCfgPath . '/predefinedUrl', $predefinedUrl);
        $meta = $this->setNestedValue($meta, $imageCfgPath . '/mediaBaseUrl', $mediaBaseUrl);

        return $meta;
    }

    private function setNestedValue(array $array, string $path, mixed $value): array
    {
        $keys = explode('/', $path);
        $current = &$array;
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        $current = $value;
        return $array;
    }
}
