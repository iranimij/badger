<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Frontend;

use Iranimij\Badger\Model\Enum\Surface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogWidget\Block\Product\ProductsList;

class RelatedProductsWidgetPlugin
{
    use StackInjectorTrait;

    public function afterGetProductDetailsHtml(
        ProductsList $subject,
        string $result,
        ProductInterface $product
    ): string {
        return $result . $this->renderStackHtml($subject->getLayout(), $product, Surface::RELATED);
    }
}
