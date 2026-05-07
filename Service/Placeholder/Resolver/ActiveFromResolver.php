<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;

class ActiveFromResolver implements PlaceholderResolverInterface
{
    public function token(): string
    {
        return 'active_from';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        return $context->badger?->getActiveFrom();
    }
}
