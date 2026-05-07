<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class DiscountAmountResolver implements PlaceholderResolverInterface
{
    public function __construct(
        private readonly PriceHelper $priceHelper
    ) {
    }

    public function token(): string
    {
        return 'discount_amount';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $product = $context->product;
        $regular = (float) $product->getPrice();
        $final = (float) ($product->getData('final_price') ?? $regular);
        if ($regular <= 0.0 || $final >= $regular) {
            return null;
        }
        return (string) $this->priceHelper->currency($regular - $final, true, false);
    }
}
