<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation\Factory;

use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;
use Iranimij\Badger\Model\Evaluation\Specification\StockStatusSpec;
use Iranimij\Badger\Model\Evaluation\SpecificationFactoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class StockStatusFactory implements SpecificationFactoryInterface
{
    public function __construct(
        private readonly StockRegistryInterface $stockRegistry
    ) {
    }

    public function create(array $args): SpecificationInterface
    {
        $expected = (string) ($args['expected'] ?? StockStatusSpec::EXPECTS_IN);
        return new StockStatusSpec($this->stockRegistry, $expected);
    }
}
