<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Service\Media;

use Iranimij\Badger\Service\Media\BadgerMediaStorage;
use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;
use Magento\Framework\File\Uploader;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;

class BadgerMediaStorageTest extends TestCase
{
    private MediaDirectoryProvider $mediaDirectory;
    private WriteInterface $writable;
    private UploaderFactory $uploaderFactory;

    protected function setUp(): void
    {
        $this->writable = $this->createMock(WriteInterface::class);
        $this->mediaDirectory = $this->createMock(MediaDirectoryProvider::class);
        $this->mediaDirectory->method('writable')->willReturn($this->writable);
        $this->mediaDirectory->method('ensureSubPath')->willReturn('/abs/iranimij/badger/user');
        $this->uploaderFactory = $this->createMock(UploaderFactory::class);
    }

    public function testUploadReturnsFileAndPath(): void
    {
        $uploader = $this->createMock(Uploader::class);
        $uploader->expects($this->once())->method('setAllowedExtensions')
            ->with(['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']);
        $uploader->method('save')->willReturn(['file' => '/sale.png']);
        $this->uploaderFactory->method('create')->willReturn($uploader);

        $storage = new BadgerMediaStorage($this->mediaDirectory, $this->uploaderFactory);
        $result = $storage->upload([
            'name' => 'sale.png', 'type' => 'image/png', 'tmp_name' => '/tmp/x',
            'error' => 0, 'size' => 10,
        ]);
        self::assertSame('sale.png', $result['file']);
        self::assertSame('iranimij/badger/user/sale.png', $result['path']);
    }

    public function testUploadThrowsWhenSaveFails(): void
    {
        $uploader = $this->createMock(Uploader::class);
        $uploader->method('save')->willReturn([]);
        $this->uploaderFactory->method('create')->willReturn($uploader);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        (new BadgerMediaStorage($this->mediaDirectory, $this->uploaderFactory))
            ->upload(['name' => 'x.png', 'type' => 'image/png', 'tmp_name' => '/tmp/x', 'error' => 0, 'size' => 0]);
    }

    public function testDeleteRejectsPathOutsideDir(): void
    {
        $this->writable->expects($this->never())->method('delete');
        $storage = new BadgerMediaStorage($this->mediaDirectory, $this->uploaderFactory);
        self::assertFalse($storage->delete('evil/hack.png'));
    }

    public function testDeleteRemovesFileInsideDir(): void
    {
        $this->writable->method('isFile')->with('iranimij/badger/user/x.png')->willReturn(true);
        $this->writable->expects($this->once())->method('delete')
            ->with('iranimij/badger/user/x.png')->willReturn(true);
        $storage = new BadgerMediaStorage($this->mediaDirectory, $this->uploaderFactory);
        self::assertTrue($storage->delete('iranimij/badger/user/x.png'));
    }

    public function testRelativeDir(): void
    {
        self::assertSame(
            'iranimij/badger/user',
            (new BadgerMediaStorage($this->mediaDirectory, $this->uploaderFactory))->relativeDir()
        );
    }
}
