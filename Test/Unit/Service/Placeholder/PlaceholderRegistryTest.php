<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderRegistry;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;
use PHPUnit\Framework\TestCase;

class PlaceholderRegistryTest extends TestCase
{
    private function resolver(string $token): PlaceholderResolverInterface
    {
        return new class($token) implements PlaceholderResolverInterface {
            public function __construct(private readonly string $t) {}
            public function token(): string { return $this->t; }
            public function resolve(string $token, PlaceholderContext $context): ?string { return $this->t; }
        };
    }

    public function testLooksUpExactToken(): void
    {
        $r = $this->resolver('price');
        $registry = new PlaceholderRegistry([$r]);
        self::assertSame($r, $registry->get('price'));
    }

    public function testReturnsNullForUnknownToken(): void
    {
        $registry = new PlaceholderRegistry([$this->resolver('price')]);
        self::assertNull($registry->get('unknown'));
    }

    public function testDuplicateTokenThrows(): void
    {
        $this->expectException(\LogicException::class);
        new PlaceholderRegistry([$this->resolver('price'), $this->resolver('price')]);
    }

    public function testPatternMatchesPrefix(): void
    {
        $attrResolver = $this->resolver('attr:*');
        $registry = new PlaceholderRegistry([$attrResolver]);
        self::assertSame($attrResolver, $registry->get('attr:color'));
        self::assertSame($attrResolver, $registry->get('attr:size'));
    }

    public function testPatternDoesNotMatchOtherTokens(): void
    {
        $registry = new PlaceholderRegistry([$this->resolver('attr:*')]);
        self::assertNull($registry->get('other:color'));
    }

    public function testTokensListsAll(): void
    {
        $registry = new PlaceholderRegistry([
            $this->resolver('price'),
            $this->resolver('attr:*'),
        ]);
        self::assertEqualsCanonicalizing(['price', 'attr:*'], $registry->tokens());
    }
}
