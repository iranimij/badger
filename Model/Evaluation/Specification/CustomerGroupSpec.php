<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class CustomerGroupSpec implements SpecificationInterface
{
    public const TOKEN = 'customer_group';

    /**
     * @param list<int> $groupIds
     */
    public function __construct(
        private readonly array $groupIds
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        return in_array($ctx->customerGroupId, array_map('intval', $this->groupIds), true);
    }
}
