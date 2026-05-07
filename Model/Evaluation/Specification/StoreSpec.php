<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class StoreSpec implements SpecificationInterface
{
    public const TOKEN = 'store';

    /**
     * @param list<int> $storeIds
     */
    public function __construct(
        private readonly array $storeIds
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        return in_array($ctx->storeId, array_map('intval', $this->storeIds), true);
    }
}
