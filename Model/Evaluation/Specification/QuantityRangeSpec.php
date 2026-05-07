<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class QuantityRangeSpec implements SpecificationInterface
{
    public const TOKEN = 'qty_range';

    public function __construct(
        private readonly ?float $min,
        private readonly ?float $max
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        if ($this->min !== null && $ctx->qty < $this->min) {
            return false;
        }
        if ($this->max !== null && $ctx->qty > $this->max) {
            return false;
        }
        return true;
    }
}
