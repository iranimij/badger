<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\ShapeRenderer;

use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Service\ShapeRenderer\ShapeCache;
use Iranimij\Badger\Service\ShapeRenderer\ShapeRendererInterface;
use Iranimij\Badger\Service\ShapeRenderer\ShapeSpec;
use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;

class ShapeCacheTest extends TestCase
{
    private MediaDirectoryProvider $mediaDirectory;
    private WriteInterface $writable;
    private ShapeRendererInterface $renderer;
    private ShapeSpec $spec;

    protected function setUp(): void
    {
        $this->writable = $this->createMock(WriteInterface::class);
        $this->mediaDirectory = $this->createMock(MediaDirectoryProvider::class);
        $this->mediaDirectory->method('writable')->willReturn($this->writable);
        $this->renderer = $this->createMock(ShapeRendererInterface::class);
        $this->spec = new ShapeSpec(
            ShapeKind::ROUNDED_RECT, 50, 30, '#fff', '#000', 1, 5, 'HI', '#000', 12
        );
    }

    public function testGeneratesFileWhenMissing(): void
    {
        $this->writable->method('isFile')->willReturn(false);
        $this->renderer->expects($this->once())->method('renderPng')
            ->with($this->spec)->willReturn('PNG-BYTES');
        $this->writable->expects($this->once())->method('writeFile')
            ->with($this->stringContains($this->spec->hash()), 'PNG-BYTES');

        $cache = new ShapeCache($this->mediaDirectory, $this->renderer);
        $path = $cache->getOrCreate($this->spec);
        self::assertStringContainsString($this->spec->hash(), $path);
        self::assertStringEndsWith('.png', $path);
    }

    public function testSkipsRenderWhenFileExists(): void
    {
        $this->writable->method('isFile')->willReturn(true);
        $this->renderer->expects($this->never())->method('renderPng');
        $this->writable->expects($this->never())->method('writeFile');

        $cache = new ShapeCache($this->mediaDirectory, $this->renderer);
        $cache->getOrCreate($this->spec);
    }

    public function testPathIsDeterministic(): void
    {
        $cache = new ShapeCache($this->mediaDirectory, $this->renderer);
        self::assertSame($cache->path($this->spec), $cache->path($this->spec));
    }

    public function testPathContainsDir(): void
    {
        $cache = new ShapeCache($this->mediaDirectory, $this->renderer);
        self::assertStringStartsWith('iranimij/badger/generated/', $cache->path($this->spec));
    }
}
