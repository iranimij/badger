<?php
declare(strict_types=1);

namespace Iranimij\Badger\Block\Surface;

use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\ResolvedBadger;
use Iranimij\Badger\ViewModel\BadgerViewModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class BadgerStack extends Template implements IdentityInterface
{
    public function __construct(
        Context $context,
        private readonly BadgerViewModel $badgerViewModel,
        private readonly StoreManagerInterface $storeManager,
        private readonly Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setProduct(?ProductInterface $product): self
    {
        $this->setData('product', $product);
        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        $product = $this->getData('product');
        if ($product instanceof ProductInterface) {
            return $product;
        }
        $current = $this->registry->registry('current_product');
        return $current instanceof ProductInterface ? $current : null;
    }

    public function setSurface(Surface|string $surface): self
    {
        $enum = $surface instanceof Surface ? $surface : $this->badgerViewModel->surfaceFromCode($surface);
        $this->setData('surface', $enum);
        return $this;
    }

    public function getSurface(): Surface
    {
        $value = $this->getData('surface');
        if ($value instanceof Surface) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            return $this->badgerViewModel->surfaceFromCode($value);
        }
        return Surface::CATEGORY_GRID;
    }

    /**
     * @return ResolvedBadger[]
     */
    public function getResolvedBadgers(): array
    {
        $product = $this->getProduct();
        if ($product === null) {
            return [];
        }
        return $this->badgerViewModel->getBadgers($product, $this->getSurface());
    }

    public function getIdentities(): array
    {
        $product = $this->getProduct();
        if ($product === null) {
            return ['iranimij_badger'];
        }
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
        } catch (\Throwable) {
            $storeId = 0;
        }
        return [
            'iranimij_badger',
            sprintf('iranimij_badger_p_%d_s_%d', (int) $product->getId(), $storeId),
        ];
    }

    public function getMediaBaseUrl(): string
    {
        try {
            return (string) $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        } catch (\Throwable) {
            return '';
        }
    }
}
