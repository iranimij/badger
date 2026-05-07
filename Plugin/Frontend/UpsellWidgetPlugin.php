<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Frontend;

use Iranimij\Badger\Model\Enum\Surface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ProductList\Upsell;

class UpsellWidgetPlugin
{
    use StackInjectorTrait;

    public function afterGetItemHtml(Upsell $subject, string $result, ProductInterface $product = null): string
    {
        return $result . $this->renderStackHtml($subject->getLayout(), $product, Surface::UPSELL);
    }
}
