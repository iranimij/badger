<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\OnSaleSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class OnSaleFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        return new OnSaleSpec();
    }
}
