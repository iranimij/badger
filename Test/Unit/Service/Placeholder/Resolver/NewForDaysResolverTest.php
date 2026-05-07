<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\NewForDaysResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class NewForDaysResolverTest extends TestCase
{
    private function resolveDays(?string $from, ?string $to): ?string
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturnMap([
            ['news_from_date', null, $from],
            ['news_to_date', null, $to],
        ]);
        return (new NewForDaysResolver())->resolve('new_for_days', new PlaceholderContext($product));
    }

    public function testReturnsDaysBetweenDates(): void
    {
        self::assertSame('7', $this->resolveDays('2026-04-01', '2026-04-08'));
    }

    public function testNullWhenFromMissing(): void
    {
        self::assertNull($this->resolveDays(null, '2026-04-08'));
    }

    public function testNullWhenToMissing(): void
    {
        self::assertNull($this->resolveDays('2026-04-01', null));
    }

    public function testNullWhenInvalid(): void
    {
        self::assertNull($this->resolveDays('not-a-date', '2026-04-08'));
    }

    public function testTokenName(): void
    {
        self::assertSame('new_for_days', (new NewForDaysResolver())->token());
    }
}
