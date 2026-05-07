<?php
declare(strict_types=1);

namespace Iranimij\Badger\ViewModel;

use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\BadgerResolver;
use Iranimij\Badger\Model\ReadModel\ResolvedBadger;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class BadgerViewModel implements ArgumentInterface
{
    public function __construct(
        private readonly BadgerResolver $resolver,
        private readonly StoreManagerInterface $storeManager,
        private readonly CustomerSession $customerSession
    ) {
    }

    /**
     * @return ResolvedBadger[]
     */
    public function getBadgers(ProductInterface $product, Surface $surface, float $qty = 1.0): array
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
        } catch (\Throwable) {
            $storeId = 0;
        }
        $groupId = (int) $this->customerSession->getCustomerGroupId();
        return $this->resolver->resolve($product, $surface, $storeId, $groupId, $qty);
    }

    public function surfaceFromCode(string $code): Surface
    {
        $upper = strtoupper($code);
        foreach (Surface::cases() as $case) {
            if ($case->name === $upper) {
                return $case;
            }
        }
        return Surface::CATEGORY_GRID;
    }
}
