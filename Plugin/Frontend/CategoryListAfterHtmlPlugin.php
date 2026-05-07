<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Frontend;

use Iranimij\Badger\Model\Enum\Surface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\View\LayoutInterface;

class CategoryListAfterHtmlPlugin
{
    use StackInjectorTrait;

    public function afterGetProductDetailsHtml(
        ListProduct $subject,
        string $result,
        ProductInterface $product
    ): string {
        return $result . $this->renderStackHtml($subject->getLayout(), $product, Surface::CATEGORY_GRID);
    }

    private function getLayout(ListProduct $subject): LayoutInterface
    {
        return $subject->getLayout();
    }
}
