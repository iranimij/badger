<?php
declare(strict_types=1);

namespace Iranimij\Badger\Ui\DataProvider;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\BadgerHydrator;
use Iranimij\Badger\Model\ResourceModel\Badger\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class BadgerFormDataProvider extends ModifierPoolDataProvider
{
    /** @var array<int, array<string, mixed>>|null */
    private ?array $loadedData = null;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly RequestInterface $request,
        private readonly BadgerRepositoryInterface $repository,
        private readonly BadgerHydrator $hydrator,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $id = (int) $this->request->getParam($this->getRequestFieldName());
        if ($id <= 0) {
            $this->loadedData = [];
            return $this->loadedData;
        }

        try {
            $badger = $this->repository->getById($id);
        } catch (NoSuchEntityException) {
            $this->loadedData = [];
            return $this->loadedData;
        }

        $row = $this->hydrator->toArray($badger);
        // Multiselect options from SystemStore return string values; cast to match.
        foreach ([BadgerInterface::STORE_IDS, BadgerInterface::CUSTOMER_GROUP_IDS] as $listField) {
            if (isset($row[$listField]) && is_array($row[$listField])) {
                $row[$listField] = array_map('strval', $row[$listField]);
            }
        }
        $row['visuals'] = ['visuals_container' => $row[BadgerInterface::VISUALS] ?? []];

        $tooltipData = is_array($row[BadgerInterface::TOOLTIP] ?? null) ? $row[BadgerInterface::TOOLTIP] : [];
        $row['tooltip'] = [
            'is_enabled'       => ($tooltipData['is_enabled'] ?? null) ? '1' : '0',
            'body_text'        => $tooltipData['body_text'] ?? '',
            'background_color' => $tooltipData['background_color'] ?? '',
            'text_color'       => $tooltipData['text_color'] ?? '',
        ];

        $this->data[$id] = $row;
        $this->loadedData = parent::getData();

        return $this->loadedData;
    }
}
