<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder\Resolver;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\Resolver\ActiveToResolver;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class ActiveToResolverTest extends TestCase
{
    public function testReturnsBadgerActiveTo(): void
    {
        $badger = $this->createMock(BadgerInterface::class);
        $badger->method('getActiveTo')->willReturn('2026-12-31 23:59:59');
        $ctx = new PlaceholderContext($this->createMock(Product::class), $badger);
        self::assertSame('2026-12-31 23:59:59', (new ActiveToResolver())->resolve('active_to', $ctx));
    }

    public function testNullWhenNoBadger(): void
    {
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertNull((new ActiveToResolver())->resolve('active_to', $ctx));
    }

    public function testTokenName(): void
    {
        self::assertSame('active_to', (new ActiveToResolver())->token());
    }
}
