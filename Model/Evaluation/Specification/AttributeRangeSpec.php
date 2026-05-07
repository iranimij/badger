<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class AttributeRangeSpec implements SpecificationInterface
{
    public const TOKEN = 'attr_range';

    public function __construct(
        private readonly string $attributeCode,
        private readonly ?float $min,
        private readonly ?float $max
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $value = $ctx->product->getData($this->attributeCode);
        if (!is_numeric($value)) {
            return false;
        }
        $n = (float) $value;
        if ($this->min !== null && $n < $this->min) {
            return false;
        }
        if ($this->max !== null && $n > $this->max) {
            return false;
        }
        return true;
    }
}
