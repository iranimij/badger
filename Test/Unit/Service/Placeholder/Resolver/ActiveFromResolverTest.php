<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\ActiveFromResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class ActiveFromResolverTest extends TestCase
{
    public function testReturnsBadgerActiveFrom(): void
    {
        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getActiveFrom')->willReturn('2026-01-01 00:00:00');
        $ctx = new PlaceholderContext($this->createMock(Product::class), $badger);
        self::assertSame('2026-01-01 00:00:00', (new ActiveFromResolver())->resolve('active_from', $ctx));
    }

    public function testNullWhenNoBadger(): void
    {
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertNull((new ActiveFromResolver())->resolve('active_from', $ctx));
    }

    public function testTokenName(): void
    {
        self::assertSame('active_from', (new ActiveFromResolver())->token());
    }
}
