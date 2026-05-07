<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\Specification\StoreSpec;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class StoreFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        $ids = $args['stores'] ?? [];
        if (!is_array($ids)) {
            throw new \InvalidArgumentException('store: "stores" must be an array.');
        }
        return new StoreSpec(array_values(array_map('intval', $ids)));
    }
}
