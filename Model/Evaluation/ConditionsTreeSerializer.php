<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\Specification\CompositeSpecification;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Core\Model\Serializer\JsonSerializer;

class ConditionsTreeSerializer
{
    public function __construct(
        private readonly JsonSerializer $jsonSerializer,
        private readonly ConditionsTreeBuilder $treeBuilder
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(string $payload): array
    {
        if ($payload === '') {
            return ['op' => CompositeSpecification::OP_AND, 'children' => []];
        }
        $decoded = $this->jsonSerializer->decodeArray($payload);
        if (!isset($decoded['op'])) {
            return ['op' => CompositeSpecification::OP_AND, 'children' => []];
        }
        return $decoded;
    }

    /**
     * @param array<string, mixed> $tree
     */
    public function encode(array $tree): string
    {
        return $this->jsonSerializer->encode($tree);
    }

    public function toSpecification(string $payload): SpecificationInterface
    {
        return $this->treeBuilder->build($this->decode($payload));
    }
}
