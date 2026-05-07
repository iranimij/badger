<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\ViewModel;

use Iranimij\Badger\Model\Enum\Surface;
use Iranimij\Badger\Model\ReadModel\BadgerResolver;
use Iranimij\Badger\ViewModel\BadgerViewModel;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class BadgerViewModelTest extends TestCase
{
    public function testDelegatesToResolverWithCurrentStoreAndGroup(): void
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(2);
        $sm = $this->createMock(StoreManagerInterface::class);
        $sm->method('getStore')->willReturn($store);
        $session = $this->createMock(CustomerSession::class);
        $session->method('getCustomerGroupId')->willReturn(3);
        $resolver = $this->createMock(BadgerResolver::class);
        $resolver->expects($this->once())->method('resolve')
            ->with($this->isInstanceOf(Product::class), Surface::CATEGORY_GRID, 2, 3, 1.0)
            ->willReturn([]);

        (new BadgerViewModel($resolver, $sm, $session))
            ->getBadgers($this->createMock(Product::class), Surface::CATEGORY_GRID);
    }

    public function testSurfaceFromCodeFallsBackToCategoryGrid(): void
    {
        $vm = new BadgerViewModel(
            $this->createMock(BadgerResolver::class),
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(CustomerSession::class)
        );
        self::assertSame(Surface::PRODUCT_PAGE, $vm->surfaceFromCode('product_page'));
        self::assertSame(Surface::CATEGORY_GRID, $vm->surfaceFromCode('unknown'));
    }

    public function testHandlesStoreException(): void
    {
        $sm = $this->createMock(StoreManagerInterface::class);
        $sm->method('getStore')->willThrowException(new \RuntimeException('no store'));
        $session = $this->createMock(CustomerSession::class);
        $session->method('getCustomerGroupId')->willReturn(0);
        $resolver = $this->createMock(BadgerResolver::class);
        $resolver->expects($this->once())->method('resolve')
            ->with($this->isInstanceOf(Product::class), Surface::CATEGORY_GRID, 0, 0, 1.0)
            ->willReturn([]);

        (new BadgerViewModel($resolver, $sm, $session))
            ->getBadgers($this->createMock(Product::class), Surface::CATEGORY_GRID);
    }
}
