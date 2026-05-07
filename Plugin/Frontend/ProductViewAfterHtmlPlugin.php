<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Frontend;

use Iranimij\Badger\Model\Enum\Surface;
use Magento\Catalog\Block\Product\View\Gallery;

class ProductViewAfterHtmlPlugin
{
    use StackInjectorTrait;

    public function afterToHtml(Gallery $subject, string $result): string
    {
        $product = $subject->getProduct();
        return $result . $this->renderStackHtml($subject->getLayout(), $product, Surface::PRODUCT_PAGE);
    }
}
