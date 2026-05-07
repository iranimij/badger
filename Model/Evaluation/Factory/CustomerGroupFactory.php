<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\CustomerGroupSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class CustomerGroupFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        $ids = $args['groups'] ?? [];
        if (!is_array($ids)) {
            throw new \InvalidArgumentException('customer_group: "groups" must be an array.');
        }
        return new CustomerGroupSpec(array_values(array_map('intval', $ids)));
    }
}
