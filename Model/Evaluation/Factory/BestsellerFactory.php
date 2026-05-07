<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface;
use Iranimij\Badger\Model\Evaluation\Specification\BestsellerSpec;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;

class BestsellerFactory implements SpecificationFactoryInterface
{
    public function __construct(
        private readonly BestsellerLookupInterface $lookup
    ) {
    }

    public function create(array $args): SpecificationInterface
    {
        $topN = isset($args['top_n']) && (int) $args['top_n'] > 0 ? (int) $args['top_n'] : 50;
        return new BestsellerSpec($this->lookup, $topN);
    }
}
