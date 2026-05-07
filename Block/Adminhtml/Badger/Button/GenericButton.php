<?php
declare(strict_types=1);

namespace Iranimij\Badger\Block\Adminhtml\Badger\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\RequestInterface;

abstract class GenericButton
{
    public function __construct(
        protected readonly Context $context,
        protected readonly RequestInterface $request
    ) {
    }

    protected function getBadgerId(): ?int
    {
        $id = (int) $this->request->getParam('badger_id');
        return $id > 0 ? $id : null;
    }

    protected function getUrl(string $route, array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
