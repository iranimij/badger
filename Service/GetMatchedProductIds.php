<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\Evaluation\ConditionsTreeBuilder;
use Iranimij\Badger\Model\Evaluation\ConditionsTreeSerializer;
use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class GetMatchedProductIds
{
    public function __construct(
        private readonly BadgerRepositoryInterface $repository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ConditionsTreeSerializer $serializer,
        private readonly ConditionsTreeBuilder $treeBuilder,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * Return all product IDs that satisfy the badger's conditions.
     *
     * @param int $badgerId
     * @param int $storeId
     * @param int $customerGroupId
     * @return int[]
     * @throws NoSuchEntityException
     */
    public function execute(int $badgerId, int $storeId = 0, int $customerGroupId = 0): array
    {
        $badger = $this->repository->getById($badgerId);
        $spec = $this->compileSpec($badger);

        $products = $this->productRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();

        $matched = [];
        foreach ($products as $product) {
            $ctx = new EvaluationContext($product, $storeId, $customerGroupId);
            if ($spec === null || $spec->isSatisfiedBy($ctx)) {
                $matched[] = (int) $product->getId();
            }
        }
        return $matched;
    }

    private function compileSpec(BadgerInterface $badger)
    {
        $payload = $badger->getConditionsPayload();
        if ($payload === null || $payload === '') {
            return null;
        }
        try {
            return $this->treeBuilder->build($this->serializer->decode($payload));
        } catch (\Throwable) {
            return null;
        }
    }
}
