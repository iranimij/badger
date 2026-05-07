<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\IsNewSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class IsNewFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        $days = isset($args['days']) && $args['days'] !== '' ? (int) $args['days'] : null;
        return new IsNewSpec($days);
    }
}
