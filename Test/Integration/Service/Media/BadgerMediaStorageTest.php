<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Service\Media;

use Iranimij\Badger\Service\Media\BadgerMediaStorage;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BadgerMediaStorageTest extends TestCase
{
    private BadgerMediaStorage $storage;
    private string $mediaRoot;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->storage = $om->get(BadgerMediaStorage::class);
        $this->mediaRoot = $om->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath();
    }

    public function testRelativeDirIsCorrect(): void
    {
        $this->assertSame('iranimij/badger/user', $this->storage->relativeDir());
    }

    public function testDeleteReturnsFalseForPathOutsideUserDir(): void
    {
        $this->assertFalse($this->storage->delete('../../etc/passwd'));
        $this->assertFalse($this->storage->delete('/etc/passwd'));
        $this->assertFalse($this->storage->delete('iranimij/badger/generated/hash.png'));
    }

    public function testDeleteReturnsFalseForNonExistentFile(): void
    {
        $this->assertFalse($this->storage->delete('iranimij/badger/user/nonexistent_xyz.png'));
    }

    public function testDeleteReturnsTrueForExistingFile(): void
    {
        $userDir = $this->mediaRoot . 'iranimij/badger/user/';
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }
        $filename = 'test_delete_' . uniqid() . '.png';
        file_put_contents($userDir . $filename, 'fake-png-content');

        $result = $this->storage->delete('iranimij/badger/user/' . $filename);
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($userDir . $filename);
    }

    public function testUploadRejectsDisallowedExtension(): void
    {
        $tmpFile = sys_get_temp_dir() . '/badger_test_' . uniqid() . '.php';
        file_put_contents($tmpFile, '<?php phpinfo(); ?>');

        $this->expectException(\Exception::class);

        try {
            $this->storage->upload([
                'name' => 'malicious.php',
                'type' => 'application/x-php',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile),
            ]);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testUploadAcceptsValidImageExtension(): void
    {
        // Create a minimal valid 1x1 PNG file
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $tmpFile = sys_get_temp_dir() . '/badger_test_' . uniqid() . '.png';
        file_put_contents($tmpFile, $png);

        try {
            $result = $this->storage->upload([
                'name' => 'test_image.png',
                'type' => 'image/png',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => strlen($png),
            ]);
            $this->assertArrayHasKey('file', $result);
            $this->assertArrayHasKey('path', $result);
            $this->assertStringContainsString('iranimij/badger/user', $result['path']);

            // Clean up uploaded file
            $this->storage->delete($result['path']);
        } finally {
            @unlink($tmpFile);
        }
    }
}
