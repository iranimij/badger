<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Model\Config;

use Iranimij\Badger\Model\Config\BadgerConfigProvider;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class ConfigProviderTest extends TestCase
{
    private BadgerConfigProvider $configProvider;
    private MutableScopeConfigInterface $mutableConfig;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->configProvider = $om->get(BadgerConfigProvider::class);
        $this->mutableConfig = $om->get(MutableScopeConfigInterface::class);
    }

    protected function tearDown(): void
    {
        $this->mutableConfig->clean();
    }

    public function testIsEnabledReturnsBoolWhenNotConfigured(): void
    {
        $result = $this->configProvider->isEnabled();
        self::assertIsBool($result);
    }

    public function testIsEnabledTrueWhenConfigSet(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_ENABLED, 1, ScopeInterface::SCOPE_STORE, 'default');
        self::assertTrue($this->configProvider->isEnabled());
    }

    public function testIsEnabledFalseWhenConfigUnset(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_ENABLED, 0, ScopeInterface::SCOPE_STORE, 'default');
        self::assertFalse($this->configProvider->isEnabled());
    }

    public function testNewForDaysReturnsDefault(): void
    {
        $days = $this->configProvider->getNewForDays();
        self::assertIsInt($days);
        self::assertGreaterThan(0, $days);
    }

    public function testNewForDaysReturnsConfiguredValue(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_NEW_FOR_DAYS, 7, ScopeInterface::SCOPE_STORE, 'default');
        self::assertSame(7, $this->configProvider->getNewForDays());
    }

    public function testTooltipEnabledReturnsBool(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_TOOLTIP_ENABLED, 1, ScopeInterface::SCOPE_STORE, 'default');
        self::assertTrue($this->configProvider->isTooltipEnabled());
    }

    public function testSelectorConfigForCategory(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_SEL_CATEGORY, '.custom-image-wrapper', ScopeInterface::SCOPE_STORE, 'default');
        self::assertSame('.custom-image-wrapper', $this->configProvider->getCategoryImageSelector());
    }

    public function testSelectorReturnsDefaultWhenNotSet(): void
    {
        self::assertSame('.product-image-photo', $this->configProvider->getCategoryImageSelector());
    }

    public function testTooltipColorConfig(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_TOOLTIP_BG, '#123456', ScopeInterface::SCOPE_STORE, 'default');
        self::assertSame('#123456', $this->configProvider->getTooltipBackgroundColor());
    }

    public function testTooltipTextColorConfig(): void
    {
        $this->mutableConfig->setValue(BadgerConfigProvider::PATH_TOOLTIP_TEXT_COLOR, '#abcdef', ScopeInterface::SCOPE_STORE, 'default');
        self::assertSame('#abcdef', $this->configProvider->getTooltipTextColor());
    }
}
