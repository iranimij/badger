<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BadgerActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        foreach ($dataSource['data']['items'] as &$item) {
            $id = (int) ($item['badger_id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl('badger/badger/edit', ['badger_id' => $id]),
                    'label' => __('Edit'),
                ],
                'duplicate' => [
                    'href' => $this->urlBuilder->getUrl('badger/badger/duplicate', ['id' => $id]),
                    'label' => __('Duplicate'),
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl('badger/badger/delete', ['badger_id' => $id]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete Badger'),
                        'message' => __('Are you sure you want to delete this badger?'),
                    ],
                    'post' => true,
                ],
            ];
        }
        return $dataSource;
    }
}
