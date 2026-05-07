<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\AttributeInSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class AttributeInFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        if (!isset($args['attribute']) || !is_string($args['attribute'])) {
            throw new \InvalidArgumentException('attr_in: "attribute" is required.');
        }
        $candidates = $args['values'] ?? [];
        if (!is_array($candidates)) {
            throw new \InvalidArgumentException('attr_in: "values" must be an array.');
        }
        return new AttributeInSpec($args['attribute'], array_values($candidates));
    }
}
