<?php
declare(strict_types=1);

namespace Iranimij\Badger\Block\Surface;

use Iranimij\Badger\Model\Config\BadgerConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class TooltipBlock extends Template
{
    protected $_template = 'Iranimij_Badger::surface/tooltip.phtml';

    public function __construct(
        Context $context,
        private readonly BadgerConfigProvider $config,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->config->isTooltipEnabled($this->getStoreId());
    }

    public function getDefaultBackgroundColor(): string
    {
        return $this->config->getTooltipBackgroundColor($this->getStoreId());
    }

    public function getDefaultTextColor(): string
    {
        return $this->config->getTooltipTextColor($this->getStoreId());
    }

    private function getStoreId(): int
    {
        try {
            return (int) $this->storeManager->getStore()->getId();
        } catch (\Throwable) {
            return 0;
        }
    }
}
