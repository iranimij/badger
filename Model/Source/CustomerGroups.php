<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class CustomerGroups implements OptionSourceInterface
{
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function toOptionArray(): array
    {
        $criteria = $this->searchCriteriaBuilder->create();
        $options = [];
        foreach ($this->groupRepository->getList($criteria)->getItems() as $group) {
            $options[] = [
                'value' => (int) $group->getId(),
                'label' => (string) $group->getCode(),
            ];
        }
        return $options;
    }
}
