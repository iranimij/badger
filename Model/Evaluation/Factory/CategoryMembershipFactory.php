<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\CategoryMembershipSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class CategoryMembershipFactory implements SpecificationFactoryInterface
{
    public function create(array $args): SpecificationInterface
    {
        $ids = $args['categories'] ?? [];
        if (!is_array($ids)) {
            throw new \InvalidArgumentException('category_in: "categories" must be an array.');
        }
        $mode = (string) ($args['mode'] ?? CategoryMembershipSpec::MODE_ANY);
        return new CategoryMembershipSpec(array_values(array_map('intval', $ids)), $mode);
    }
}
