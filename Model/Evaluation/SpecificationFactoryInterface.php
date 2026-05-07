<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;

interface SpecificationFactoryInterface
{
    /**
     * @param array<string, mixed> $args
     */
    public function create(array $args): SpecificationInterface;
}
