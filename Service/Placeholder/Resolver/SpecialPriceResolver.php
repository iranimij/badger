<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class SpecialPriceResolver implements PlaceholderResolverInterface
{
    public function __construct(
        private readonly PriceHelper $priceHelper
    ) {
    }

    public function token(): string
    {
        return 'special_price';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $special = $context->product->getData('special_price');
        if ($special === null || $special === '') {
            return null;
        }
        return (string) $this->priceHelper->currency((float) $special, true, false);
    }
}
