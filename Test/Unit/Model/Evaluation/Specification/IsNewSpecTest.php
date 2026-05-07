<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\IsNewSpec;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

class IsNewSpecTest extends TestCase
{
    /** @param array<string, mixed> $data */
    private function ctxFor(array $data): EvaluationContext
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturnCallback(fn (string $k) => $data[$k] ?? null);
        return new EvaluationContext($product, 1, 0);
    }

    public function testNewsFromDateInRangeQualifies(): void
    {
        $spec = new IsNewSpec();
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([
            'news_from_date' => date('Y-m-d H:i:s', time() - 86400),
            'news_to_date' => date('Y-m-d H:i:s', time() + 86400),
        ])));
    }

    public function testNewsToDateInPast(): void
    {
        $spec = new IsNewSpec();
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([
            'news_from_date' => date('Y-m-d H:i:s', time() - 172800),
            'news_to_date' => date('Y-m-d H:i:s', time() - 86400),
        ])));
    }

    public function testRecencyDaysFallback(): void
    {
        $spec = new IsNewSpec(30);
        self::assertTrue($spec->isSatisfiedBy($this->ctxFor([
            'created_at' => date('Y-m-d H:i:s', time() - 86400),
        ])));
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([
            'created_at' => date('Y-m-d H:i:s', time() - 86400 * 60),
        ])));
    }

    public function testNoDataReturnsFalse(): void
    {
        $spec = new IsNewSpec();
        self::assertFalse($spec->isSatisfiedBy($this->ctxFor([])));
    }
}
