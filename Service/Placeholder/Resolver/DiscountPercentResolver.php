<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;

class DiscountPercentResolver implements PlaceholderResolverInterface
{
    public function token(): string
    {
        return 'discount_percent';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $product = $context->product;
        $regular = (float) $product->getPrice();
        $final = (float) ($product->getData('final_price') ?? $regular);
        if ($regular <= 0.0 || $final >= $regular) {
            return null;
        }
        $percent = (int) round(100.0 * ($regular - $final) / $regular);
        return $percent . '%';
    }
}
