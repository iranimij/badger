<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;

class QtyResolver implements PlaceholderResolverInterface
{
    public function token(): string
    {
        return 'qty';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $qty = $context->qty;
        return ($qty == (int) $qty) ? (string) (int) $qty : (string) $qty;
    }
}
