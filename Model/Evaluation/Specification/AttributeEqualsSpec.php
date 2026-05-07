<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class AttributeEqualsSpec implements SpecificationInterface
{
    public const TOKEN = 'attr_eq';

    public function __construct(
        private readonly string $attributeCode,
        private readonly mixed $expected
    ) {
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        $value = $ctx->product->getData($this->attributeCode);
        if (is_scalar($value) && is_scalar($this->expected)) {
            return (string) $value === (string) $this->expected;
        }
        return $value === $this->expected;
    }
}
