<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;

class NewForDaysResolver implements PlaceholderResolverInterface
{
    public function token(): string
    {
        return 'new_for_days';
    }

    public function resolve(string $token, PlaceholderContext $context): ?string
    {
        $from = $context->product->getData('news_from_date');
        $to = $context->product->getData('news_to_date');
        if (!$from || !$to) {
            return null;
        }
        try {
            $start = new \DateTimeImmutable((string) $from);
            $end = new \DateTimeImmutable((string) $to);
        } catch (\Throwable) {
            return null;
        }
        $days = (int) $start->diff($end)->days;
        return $days > 0 ? (string) $days : null;
    }
}
