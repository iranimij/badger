<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Adminhtml\Badger\Image;

use Iranimij\Badger\Controller\Adminhtml\Badger\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class GetPredefined extends AbstractAction implements HttpGetActionInterface
{
    private const MODULE_PREDEFINED_DIR = 'Iranimij_Badger::images/predefined';
    private const MEDIA_PREDEFINED_DIR  = 'iranimij/badger/predefined';

    private static array $moduleImages = [
        ['file' => 'sale-red.svg',    'label' => 'Sale'],
        ['file' => 'new-green.svg',   'label' => 'New'],
        ['file' => 'hot-orange.svg',  'label' => 'Hot'],
        ['file' => 'best-blue.svg',   'label' => 'Best'],
        ['file' => 'promo-purple.svg','label' => 'Promo'],
        ['file' => 'eco-green.svg',   'label' => 'Eco'],
    ];

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly AssetRepository $assetRepo,
        private readonly Filesystem $filesystem
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $images = [];

        // 1 — Module-bundled static SVG badges
        foreach (self::$moduleImages as $entry) {
            try {
                $url = $this->assetRepo->getUrl(
                    self::MODULE_PREDEFINED_DIR . '/' . $entry['file']
                );
                $images[] = [
                    'path'  => $url,
                    'url'   => $url,
                    'label' => $entry['label'],
                    'type'  => 'static',
                ];
            } catch (\Throwable) {
                // asset not deployed yet — skip
            }
        }

        // 2 — User-placed images under pub/media/iranimij/badger/predefined/
        $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $relDir = self::MEDIA_PREDEFINED_DIR;
        if ($mediaDir->isDirectory($relDir)) {
            foreach ($mediaDir->read($relDir) as $relPath) {
                $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
                if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true)) {
                    continue;
                }
                $basename = basename($relPath);
                $label    = ucfirst(str_replace(['-', '_', '.' . $ext], [' ', ' ', ''], $basename));
                $images[] = [
                    'path'  => $relPath,
                    'url'   => $this->_url->getBaseUrl(['_type' => 'media']) . $relPath,
                    'label' => $label,
                    'type'  => 'media',
                ];
            }
        }

        return $this->jsonFactory->create()->setData(['images' => $images]);
    }
}
