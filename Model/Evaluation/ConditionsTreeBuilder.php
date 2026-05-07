<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\Specification\CompositeSpecification;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;

class ConditionsTreeBuilder
{
    public function __construct(
        private readonly SpecificationRegistry $registry
    ) {
    }

    /**
     * @param array<string, mixed> $node
     */
    public function build(array $node): SpecificationInterface
    {
        $op = isset($node['op']) ? (string) $node['op'] : '';
        if ($op === CompositeSpecification::OP_AND || $op === CompositeSpecification::OP_OR) {
            $children = [];
            foreach ($node['children'] ?? [] as $childNode) {
                if (!is_array($childNode)) {
                    throw new \InvalidArgumentException('Composite child must be an array.');
                }
                $children[] = $this->build($childNode);
            }
            return new CompositeSpecification($op, $children);
        }

        if (!isset($node['token']) || !is_string($node['token'])) {
            throw new \InvalidArgumentException('Leaf node must have a string "token".');
        }

        $args = [];
        if (isset($node['args']) && is_array($node['args'])) {
            $args = $node['args'];
        }

        return $this->registry->get($node['token'])->create($args);
    }
}
