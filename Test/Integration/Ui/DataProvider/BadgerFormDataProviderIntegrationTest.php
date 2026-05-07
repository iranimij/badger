<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Ui\DataProvider;

use Iranimij\Badger\Ui\DataProvider\BadgerFormDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class BadgerFormDataProviderIntegrationTest extends TestCase
{
    private BadgerFormDataProvider $provider;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();

        $request = $om->get(RequestInterface::class);
        $request->setParam('badger_id', 0);

        $this->provider = $om->create(BadgerFormDataProvider::class, [
            'name'               => 'iranimij_badger_form_data_source',
            'primaryFieldName'   => 'badger_id',
            'requestFieldName'   => 'badger_id',
            'data'               => [
                'config' => [
                    'submit_url' => 'https://app.magento.test/admin/badger/badger/save/key/test/',
                ],
            ],
        ]);
    }

    public function testGetConfigDataPreservesSubmitUrl(): void
    {
        $this->provider->getData();

        $config = $this->provider->getConfigData();
        $this->assertArrayHasKey(
            'submit_url',
            $config,
            'submit_url must survive getData() — the form POSTs to this URL on save'
        );
        $this->assertStringContainsString('badger/save', $config['submit_url']);
    }

    /**
     * @magentoConfigFixture default_store iranimij_badger/tooltip/enabled 1
     */
    public function testGetDataReturnsEmptyForNewBadge(): void
    {
        $data = $this->provider->getData();
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }
}
