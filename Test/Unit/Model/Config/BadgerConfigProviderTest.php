<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Model\Config;

use Iranimij\Badger\Model\Config\BadgerConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class BadgerConfigProviderTest extends TestCase
{
    public function testIsEnabledReadsFlag(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->method('isSetFlag')
            ->with(BadgerConfigProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(true);
        self::assertTrue((new BadgerConfigProvider($scope))->isEnabled(1));
    }

    public function testNewForDaysFallsBackToDefault(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->method('getValue')->willReturn(null);
        self::assertSame(14, (new BadgerConfigProvider($scope))->getNewForDays(1));
    }

    public function testNewForDaysReadsScopedValue(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->method('getValue')
            ->with(BadgerConfigProvider::PATH_NEW_FOR_DAYS, ScopeInterface::SCOPE_STORE, 2)
            ->willReturn('30');
        self::assertSame(30, (new BadgerConfigProvider($scope))->getNewForDays(2));
    }

    public function testTooltipColorsReturnDefaults(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->method('getValue')->willReturn(null);
        $provider = new BadgerConfigProvider($scope);
        self::assertSame('#222222', $provider->getTooltipBackgroundColor());
        self::assertSame('#ffffff', $provider->getTooltipTextColor());
    }

    public function testCategorySelectorReadsScoped(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->method('getValue')
            ->with(BadgerConfigProvider::PATH_SEL_CATEGORY, ScopeInterface::SCOPE_STORE, null)
            ->willReturn('.product-card img');
        self::assertSame('.product-card img', (new BadgerConfigProvider($scope))->getCategoryImageSelector());
    }
}
