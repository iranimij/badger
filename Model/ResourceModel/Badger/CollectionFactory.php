<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ResourceModel\Badger;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    public function __construct(
        protected ObjectManagerInterface $objectManager,
        protected string $instanceName = Collection::class
    ) {
    }

    public function create(array $data = []): Collection
    {
        /** @var Collection $instance */
        $instance = $this->objectManager->create($this->instanceName, $data);
        return $instance;
    }
}
