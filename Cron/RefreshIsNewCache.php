<?php
declare(strict_types=1);

namespace Iranimij\Badger\Cron;

use Magento\Framework\App\Cache\TypeListInterface;
use Psr\Log\LoggerInterface;

class RefreshIsNewCache
{
    public const CACHE_TAG = 'iranimij_badger';

    public function __construct(
        private readonly TypeListInterface $cacheTypeList,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            $this->cacheTypeList->invalidate(['block_html', 'full_page']);
        } catch (\Throwable $e) {
            $this->logger->error('iranimij_badger_isnew_refresh failed: ' . $e->getMessage());
        }
    }
}
