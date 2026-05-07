<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Frontend;

use Iranimij\Badger\Model\Enum\Surface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Block\Cart\Crosssell;

class CartCrossSellPlugin
{
    use StackInjectorTrait;

    public function afterGetItemHtml(Crosssell $subject, string $result, ProductInterface $product = null): string
    {
        return $result . $this->renderStackHtml($subject->getLayout(), $product, Surface::CART_CROSSSELL);
    }
}
