<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Placeholder;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderRegistry;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;
use Iranimij\Badger\Service\Placeholder\TemplateRenderer;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class TemplateRendererTest extends TestCase
{
    private function makeResolver(string $token, ?string $value): PlaceholderResolverInterface
    {
        return new class($token, $value) implements PlaceholderResolverInterface {
            public function __construct(private readonly string $t, private readonly ?string $v) {}
            public function token(): string { return $this->t; }
            public function resolve(string $token, PlaceholderContext $context): ?string { return $this->v; }
        };
    }

    public function testPassthroughWithoutTokens(): void
    {
        $renderer = new TemplateRenderer(new PlaceholderRegistry([]));
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertSame('plain text', $renderer->render('plain text', $ctx));
    }

    public function testSubstitutesRegisteredToken(): void
    {
        $registry = new PlaceholderRegistry([$this->makeResolver('price', '$9.99')]);
        $renderer = new TemplateRenderer($registry);
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertSame('Now $9.99!', $renderer->render('Now {{price}}!', $ctx));
    }

    public function testLeavesUnknownTokenIntact(): void
    {
        $renderer = new TemplateRenderer(new PlaceholderRegistry([]));
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertSame('{{unknown}}', $renderer->render('{{unknown}}', $ctx));
    }

    public function testNullResolverOutputBecomesEmptyString(): void
    {
        $registry = new PlaceholderRegistry([$this->makeResolver('special_price', null)]);
        $renderer = new TemplateRenderer($registry);
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertSame('x:', $renderer->render('x:{{special_price}}', $ctx));
    }

    public function testMultipleTokens(): void
    {
        $registry = new PlaceholderRegistry([
            $this->makeResolver('price', '$10'),
            $this->makeResolver('sku', 'ABC-1'),
        ]);
        $renderer = new TemplateRenderer($registry);
        $ctx = new PlaceholderContext($this->createMock(Product::class));
        self::assertSame('ABC-1 for $10', $renderer->render('{{sku}} for {{price}}', $ctx));
    }

    public function testPatternTokenReceivesFullToken(): void
    {
        $seen = null;
        $resolver = new class($seen) implements PlaceholderResolverInterface {
            public ?string $seen = null;
            public function __construct(&$seen) { $this->seen = &$seen; }
            public function token(): string { return 'attr:*'; }
            public function resolve(string $token, PlaceholderContext $context): ?string
            {
                $this->seen = $token;
                return 'v';
            }
        };
        $renderer = new TemplateRenderer(new PlaceholderRegistry([$resolver]));
        $renderer->render('{{attr:color}}', new PlaceholderContext($this->createMock(Product::class)));
        self::assertSame('attr:color', $resolver->seen);
    }
}
