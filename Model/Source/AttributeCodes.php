<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class AttributeCodes implements OptionSourceInterface
{
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function toOptionArray(): array
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('is_user_defined', 1)
            ->create();

        $options = [];
        foreach ($this->attributeRepository->getList($criteria)->getItems() as $attribute) {
            $code = (string) $attribute->getAttributeCode();
            $label = $attribute->getDefaultFrontendLabel() ?: $code;
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
