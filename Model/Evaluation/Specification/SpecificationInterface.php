<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;

interface SpecificationInterface
{
    public function isSatisfiedBy(EvaluationContext $ctx): bool;
}
