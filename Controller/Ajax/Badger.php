<?php
declare(strict_types=1);

namespace Iranimij\Badger\Controller\Ajax;

use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\BadgerResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Badger implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $jsonFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly BadgerResolver $resolver,
        private readonly StoreManagerInterface $storeManager,
        private readonly CustomerSession $customerSession
    ) {
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $productId = (int) $this->request->getParam('product_id');
        $surfaceCode = (string) $this->request->getParam('surface', 'CATEGORY_GRID');

        if (!$productId) {
            return $result->setData(['error' => 'Missing product_id', 'badgers' => []]);
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException) {
            return $result->setData(['error' => 'Product not found', 'badgers' => []]);
        }

        $surface = $this->surfaceFromCode($surfaceCode);

        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
        } catch (\Throwable) {
            $storeId = 0;
        }
        $groupId = (int) $this->customerSession->getCustomerGroupId();

        $resolved = $this->resolver->resolve($product, $surface, $storeId, $groupId);

        $payload = [];
        foreach ($resolved as $badger) {
            $payload[] = [
                'badger_id'   => $badger->badgerId,
                'label_text'  => $badger->labelText,
                'image_path'  => $badger->imagePath,
                'alt_text'    => $badger->altText,
                'css_class'   => $badger->cssClass,
                'placement'   => $badger->placement->cssClass(),
                'shape'       => strtolower($badger->shape->name),
                'size_percent'=> $badger->sizePercent,
                'redirect_url'=> $badger->redirectUrl,
                'tooltip_text'=> $badger->tooltipText,
                'tooltip_bg'  => $badger->tooltipBgColor,
                'tooltip_color' => $badger->tooltipTextColor,
            ];
        }

        return $result->setData(['badgers' => $payload]);
    }

    private function surfaceFromCode(string $code): Surface
    {
        $upper = strtoupper($code);
        foreach (Surface::cases() as $case) {
            if ($case->name === $upper) {
                return $case;
            }
        }
        return Surface::CATEGORY_GRID;
    }
}
