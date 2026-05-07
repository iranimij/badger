<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Indexer;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\Rule;
use Iranimij\Badger\Model\RuleFactory;
use Iranimij\Core\Model\Serializer\JsonSerializer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\StoreRepositoryInterface;

class IndexBuilder
{
    private const INDEX_TABLE = 'iranimij_badger_index';

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly BadgerRepositoryInterface $badgerRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly RuleFactory $ruleFactory,
        private readonly JsonSerializer $jsonSerializer
    ) {
    }

    public function rebuildAll(): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::INDEX_TABLE);
        $connection->delete($table);
        $this->buildForBadgers($this->loadEnabledBadgers());
    }

    /**
     * @param int[] $productIds
     */
    public function rebuildForProducts(array $productIds): void
    {
        if ($productIds === []) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::INDEX_TABLE);
        $connection->delete($table, ['product_id IN (?)' => $productIds]);
        $this->buildForBadgers($this->loadEnabledBadgers(), $productIds);
    }

    /**
     * @param int[] $badgerIds
     */
    public function rebuildForBadgers(array $badgerIds): void
    {
        if ($badgerIds === []) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::INDEX_TABLE);
        $connection->delete($table, ['badger_id IN (?)' => $badgerIds]);
        $loaded = [];
        foreach ($badgerIds as $id) {
            try {
                $badger = $this->badgerRepository->getById((int) $id);
                if ($badger->isEnabled()) {
                    $loaded[] = $badger;
                }
            } catch (\Throwable) {
                continue;
            }
        }
        $this->buildForBadgers($loaded);
    }

    /**
     * @return BadgerInterface[]
     */
    private function loadEnabledBadgers(): array
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(BadgerInterface::IS_ENABLED, 1)
            ->create();
        return $this->badgerRepository->getList($criteria)->getItems();
    }

    /**
     * @param BadgerInterface[] $badgers
     * @param int[] $productIds
     */
    private function buildForBadgers(array $badgers, array $productIds = []): void
    {
        if ($badgers === []) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::INDEX_TABLE);

        $stores = array_map(static fn ($s) => (int) $s->getId(), $this->storeRepository->getList());
        $groupCriteria = $this->searchCriteriaBuilder->create();
        $groups = array_map(static fn ($g) => (int) $g->getId(), $this->groupRepository->getList($groupCriteria)->getItems());
        $products = $this->resolveProducts($productIds);

        $rows = [];
        foreach ($badgers as $badger) {
            $rule = $this->buildRule($badger);
            $allowedStores = $this->intersect($stores, array_map('intval', $badger->getStoreIds()));
            $allowedGroups = $this->intersect($groups, array_map('intval', $badger->getCustomerGroupIds()));
            foreach ($products as $product) {
                $productId = (int) $product->getId();
                if ($rule !== null && !$rule->validate($product)) {
                    continue;
                }
                foreach ($allowedStores as $storeId) {
                    foreach ($allowedGroups as $groupId) {
                        $rows[] = [
                            'badger_id' => (int) $badger->getBadgerId(),
                            'product_id' => $productId,
                            'store_id' => $storeId,
                            'customer_group_id' => $groupId,
                        ];
                    }
                }
            }
        }

        if ($rows !== []) {
            $connection->insertMultiple($table, $rows);
        }
    }

    private function buildRule(BadgerInterface $badger): ?Rule
    {
        $payload = $badger->getConditionsPayload();
        if ($payload === null || $payload === '') {
            return null;
        }
        try {
            $decoded = $this->jsonSerializer->decodeArray($payload);
            if ($decoded === []) {
                return null;
            }
            $rule = $this->ruleFactory->create();
            $rule->loadPost(['conditions' => $decoded]);
            return $rule;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param int[] $all
     * @param int[] $explicit
     * @return int[]
     */
    private function intersect(array $all, array $explicit): array
    {
        return $explicit === [] ? $all : array_values(array_intersect($all, $explicit));
    }

    /**
     * @param int[] $productIds
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    private function resolveProducts(array $productIds): array
    {
        if ($productIds === []) {
            $criteria = $this->searchCriteriaBuilder->create();
            return $this->productRepository->getList($criteria)->getItems();
        }
        $products = [];
        foreach ($productIds as $id) {
            try {
                $products[] = $this->productRepository->getById((int) $id);
            } catch (\Throwable) {
                continue;
            }
        }
        return $products;
    }
}
