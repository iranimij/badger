<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\AttributeRangeSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class AttributeRangeFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        if (!isset($args['attribute']) || !is_string($args['attribute'])) {
            throw new \InvalidArgumentException('attr_range: "attribute" is required.');
        }
        $min = isset($args['min']) && $args['min'] !== '' ? (float) $args['min'] : null;
        $max = isset($args['max']) && $args['max'] !== '' ? (float) $args['max'] : null;
        return new AttributeRangeSpec($args['attribute'], $min, $max);
    }
}
