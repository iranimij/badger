<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface;
use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class BestsellerSpec implements SpecificationInterface
{
    public const TOKEN = 'bestseller';

    public function __construct(
        private readonly BestsellerLookupInterface $lookup,
        private readonly int $topN = 50
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $productId = (int) $ctx->product->getId();
        if ($productId <= 0) {
            return false;
        }
        return $this->lookup->isBestseller($productId, $ctx->storeId, $this->topN);
    }
}
