<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\AttributeEqualsSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class AttributeEqualsFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        if (!isset($args['attribute']) || !is_string($args['attribute'])) {
            throw new \InvalidArgumentException('attr_eq: "attribute" is required.');
        }
        if (!array_key_exists('value', $args)) {
            throw new \InvalidArgumentException('attr_eq: "value" is required.');
        }
        return new AttributeEqualsSpec($args['attribute'], $args['value']);
    }
}
