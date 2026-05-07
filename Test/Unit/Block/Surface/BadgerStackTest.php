<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Block\Surface;

use Iranimij\Badger\Block\Surface\BadgerStack;
use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\ViewModel\BadgerViewModel;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class BadgerStackTest extends TestCase
{
    private BadgerStack $block;
    private BadgerViewModel $viewModel;
    private StoreManagerInterface $storeManager;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventManager')->willReturn($this->createMock(ManagerInterface::class));
        $context->method('getUrlBuilder')->willReturn($this->createMock(Url::class));

        $this->viewModel = $this->createMock(BadgerViewModel::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->block = new BadgerStack($context, $this->viewModel, $this->storeManager);
    }

    public function testGetIdentitiesIncludesPerProductTag(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(7);
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(2);
        $this->storeManager->method('getStore')->willReturn($store);

        $this->block->setProduct($product);
        self::assertSame(['iranimij_badger', 'iranimij_badger_p_7_s_2'], $this->block->getIdentities());
    }

    public function testGetIdentitiesWithoutProduct(): void
    {
        self::assertSame(['iranimij_badger'], $this->block->getIdentities());
    }

    public function testReturnsEmptyWhenNoProduct(): void
    {
        $this->viewModel->expects($this->never())->method('getBadgers');
        self::assertSame([], $this->block->getResolvedBadgers());
    }

    public function testDelegatesToViewModel(): void
    {
        $product = $this->createMock(Product::class);
        $this->block->setProduct($product);
        $this->block->setSurface(Surface::PRODUCT_PAGE);
        $this->viewModel->expects($this->once())->method('getBadgers')
            ->with($product, Surface::PRODUCT_PAGE)->willReturn([]);
        $this->block->getResolvedBadgers();
    }

    public function testSurfaceFromString(): void
    {
        $this->viewModel->method('surfaceFromCode')->with('PRODUCT_PAGE')->willReturn(Surface::PRODUCT_PAGE);
        $this->block->setSurface('PRODUCT_PAGE');
        self::assertSame(Surface::PRODUCT_PAGE, $this->block->getSurface());
    }
}
