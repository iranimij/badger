<?php
declare(strict_types=1);

namespace Iranimij\Badger\Plugin\Frontend;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Model\Enum\Surface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\LayoutInterface;

trait StackInjectorTrait
{
    private function renderStackHtml(LayoutInterface $layout, ?ProductInterface $product, Surface $surface): string
    {
        if ($product === null) {
            return '';
        }
        /** @var BadgerStack $block */
        $block = $layout->createBlock(BadgerStack::class);
        $block->setTemplate('Iranimij_Badger::surface/stack.phtml');
        $block->setProduct($product);
        $block->setSurface($surface);
        return (string) $block->toHtml();
    }
}
