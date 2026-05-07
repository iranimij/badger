<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Media;

use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\File\UploaderFactory;

class BadgerMediaStorage
{
    private const REL_DIR = 'iranimij/badger/user';
    private const ALLOWED = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];

    public function __construct(
        private readonly MediaDirectoryProvider $mediaDirectory,
        private readonly UploaderFactory $uploaderFactory
    ) {
    }

    /**
     * @param string $fileKey Key in $_FILES (e.g. 'image')
     * @return array{file:string,path:string}
     */
    public function upload(string $fileKey): array
    {
        /** @var Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileKey]);
        $uploader->setAllowedExtensions(self::ALLOWED);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        $absoluteDir = $this->mediaDirectory->ensureSubPath(self::REL_DIR);
        $result = $uploader->save($absoluteDir);
        if (!is_array($result) || empty($result['file'])) {
            throw new LocalizedException(__('File upload failed.'));
        }

        $file = ltrim((string) $result['file'], '/');
        return [
            'file' => $file,
            'path' => self::REL_DIR . '/' . $file,
        ];
    }

    public function delete(string $relativePath): bool
    {
        $writable = $this->mediaDirectory->writable();
        $normalized = ltrim($relativePath, '/');
        if (!str_starts_with($normalized, self::REL_DIR . '/')) {
            return false;
        }
        if ($writable->isFile($normalized)) {
            return $writable->delete($normalized);
        }
        return false;
    }

    public function relativeDir(): string
    {
        return self::REL_DIR;
    }
}
