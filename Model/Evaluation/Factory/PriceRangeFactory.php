<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\PriceRangeSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class PriceRangeFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        $min = isset($args['min']) && $args['min'] !== '' ? (float) $args['min'] : null;
        $max = isset($args['max']) && $args['max'] !== '' ? (float) $args['max'] : null;
        $field = (string) ($args['field'] ?? PriceRangeSpec::FIELD_FINAL);
        return new PriceRangeSpec($min, $max, $field);
    }
}
