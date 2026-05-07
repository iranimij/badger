<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder;

interface PlaceholderResolverInterface
{
    public function token(): string;

    public function resolve(string $token, PlaceholderContext $context): ?string;
}
