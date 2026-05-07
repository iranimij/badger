<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;

class ProductAttributeResolver implements PlaceholderResolverInterface
{
    public function token(): string
    {
        return 'attr:*';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $code = '';
        if (str_starts_with($token, 'attr:')) {
            $code = substr($token, 5);
        }
        if ($code === '') {
            return null;
        }
        $value = $context->product->getData($code);
        if ($value === null || $value === '' || is_array($value)) {
            return null;
        }
        return (string) $value;
    }
}
