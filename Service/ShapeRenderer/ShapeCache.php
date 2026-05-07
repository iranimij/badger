<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\ShapeRenderer;

use Iranimij\Core\Service\Filesystem\MediaDirectoryProvider;

class ShapeCache
{
    private const REL_DIR = 'iranimij/badger/generated';

    public function __construct(
        private readonly MediaDirectoryProvider $mediaDirectory,
        private readonly ShapeRendererInterface $renderer
    ) {
    }

    public function getOrCreate(ShapeSpec $spec): string
    {
        $hash = $spec->hash();
        $relative = self::REL_DIR . '/' . $hash . '.png';
        $writable = $this->mediaDirectory->writable();
        $this->mediaDirectory->ensureSubPath(self::REL_DIR);

        if (!$writable->isFile($relative)) {
            $bytes = $this->renderer->renderPng($spec);
            $writable->writeFile($relative, $bytes);
        }

        return $relative;
    }

    public function path(ShapeSpec $spec): string
    {
        return self::REL_DIR . '/' . $spec->hash() . '.png';
    }
}
