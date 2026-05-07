<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Service\ShapeRenderer;

use Iranimij\Badger\Service\ShapeRenderer\ShapeCache;
use Iranimij\Badger\Service\ShapeRenderer\ShapeSpec;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ShapeCacheTest extends TestCase
{
    private ShapeCache $cache;

    protected function setUp(): void
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('ext-gd not available');
        }
        $this->cache = Bootstrap::getObjectManager()->get(ShapeCache::class);
    }

    public function testGetOrCreateReturnsRelativePath(): void
    {
        $spec = $this->makeSpec('SALE');
        $path = $this->cache->getOrCreate($spec);

        self::assertIsString($path);
        self::assertStringContainsString('iranimij/badger/generated/', $path);
        self::assertStringEndsWith('.png', $path);
    }

    public function testSameSpecReturnsSamePath(): void
    {
        $spec = $this->makeSpec('SAME');
        $path1 = $this->cache->getOrCreate($spec);
        $path2 = $this->cache->getOrCreate($spec);

        self::assertSame($path1, $path2);
    }

    public function testDifferentSpecReturnsDifferentPath(): void
    {
        $specA = $this->makeSpec('SALE');
        $specB = $this->makeSpec('NEW');

        $pathA = $this->cache->path($specA);
        $pathB = $this->cache->path($specB);

        self::assertNotSame($pathA, $pathB);
    }

    public function testPathMatchesGetOrCreate(): void
    {
        $spec = $this->makeSpec('MATCH');
        $created = $this->cache->getOrCreate($spec);
        $path = $this->cache->path($spec);

        self::assertSame($path, $created);
    }

    public function testHashIsStable(): void
    {
        $spec = $this->makeSpec('STABLE');
        $hash1 = $spec->hash();
        $hash2 = $spec->hash();

        self::assertSame($hash1, $hash2);
    }

    private function makeSpec(string $text): ShapeSpec
    {
        return new ShapeSpec(
            kind: ShapeKind::ROUNDED_RECT,
            width: 80,
            height: 30,
            fillColor: '#cc0000',
            borderColor: '#990000',
            borderWidth: 0,
            cornerRadius: 4,
            text: $text,
            textColor: '#ffffff',
            fontSize: 10
        );
    }
}
