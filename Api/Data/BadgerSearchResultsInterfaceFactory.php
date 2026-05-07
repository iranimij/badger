<?php
declare(strict_types=1);

namespace Iranimij\Badger\Api\Data;

use Magento\Framework\ObjectManagerInterface;

class BadgerSearchResultsInterfaceFactory
{
    public function __construct(
        protected ObjectManagerInterface $objectManager,
        protected string $instanceName = BadgerSearchResultsInterface::class
    ) {
    }

    public function create(array $data = []): BadgerSearchResultsInterface
    {
        /** @var BadgerSearchResultsInterface $instance */
        $instance = $this->objectManager->create($this->instanceName, $data);
        return $instance;
    }
}
