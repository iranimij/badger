<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

class CompositeSpecification implements SpecificationInterface
{
    public const OP_AND = 'and';
    public const OP_OR = 'or';

    /**
     * @param SpecificationInterface[] $children
     */
    public function __construct(
        private readonly string $operator,
        private readonly array $children
    ) {
        if ($operator !== self::OP_AND && $operator !== self::OP_OR) {
            throw new \InvalidArgumentException("Unsupported operator: $operator");
        }
    }

    public function isSatisfiedBy(EvaluationContext $ctx): bool
    {
        if ($this->children === []) {
            return $this->operator === self::OP_AND;
        }

        foreach ($this->children as $child) {
            $result = $child->isSatisfiedBy($ctx);
            if ($this->operator === self::OP_AND && !$result) {
                return false;
            }
            if ($this->operator === self::OP_OR && $result) {
                return true;
            }
        }

        return $this->operator === self::OP_AND;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    /** @return SpecificationInterface[] */
    public function getChildren(): array
    {
        return $this->children;
    }
}
