<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class PriceResolver implements PlaceholderResolverInterface
{
    public function __construct(
        private readonly PriceHelper $priceHelper
    ) {
    }

    public function token(): string
    {
        return 'price';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $product = $context->product;
        $final = $product->getData('final_price');
        $price = $final !== null ? (float) $final : (float) $product->getPrice();
        return (string) $this->priceHelper->currency($price, true, false);
    }
}
