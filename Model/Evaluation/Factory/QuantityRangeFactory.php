<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\QuantityRangeSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class QuantityRangeFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        $min = isset($args['min']) && $args['min'] !== '' ? (float) $args['min'] : null;
        $max = isset($args['max']) && $args['max'] !== '' ? (float) $args['max'] : null;
        return new QuantityRangeSpec($min, $max);
    }
}
