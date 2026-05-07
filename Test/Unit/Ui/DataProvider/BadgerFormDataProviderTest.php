<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit\Ui\DataProvider;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Model\BadgerHydrator;
use Iranimij\Badger\Model\ResourceModel\Badger\Collection;
use Iranimij\Badger\Model\ResourceModel\Badger\CollectionFactory;
use Iranimij\Badger\Ui\DataProvider\BadgerFormDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use PHPUnit\Framework\TestCase;

class BadgerFormDataProviderTest extends TestCase
{
    private BadgerFormDataProvider $provider;
    private RequestInterface $request;
    private BadgerRepositoryInterface $repository;
    private BadgerHydrator $hydrator;

    protected function setUp(): void
    {
        $collection = $this->createMock(Collection::class);
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $this->request = $this->createMock(RequestInterface::class);
        $this->repository = $this->createMock(BadgerRepositoryInterface::class);
        $this->hydrator = $this->createMock(BadgerHydrator::class);

        $pool = $this->createMock(PoolInterface::class);
        $pool->method('getModifiersInstances')->willReturn([]);

        $this->provider = new BadgerFormDataProvider(
            'iranimij_badger_form_data_source',
            'badger_id',
            'badger_id',
            $factory,
            $this->request,
            $this->repository,
            $this->hydrator,
            [],
            [],
            $pool
        );
    }

    public function testReturnsEmptyWhenNoId(): void
    {
        $this->request->method('getParam')->with('badger_id')->willReturn(0);
        $this->repository->expects($this->never())->method('getById');
        self::assertSame([], $this->provider->getData());
    }

    public function testReturnsEmptyOnNotFound(): void
    {
        $this->request->method('getParam')->with('badger_id')->willReturn(42);
        $this->repository->method('getById')->with(42)
            ->willThrowException(new NoSuchEntityException());
        self::assertSame([], $this->provider->getData());
    }

    public function testLoadsAndKeysById(): void
    {
        $badger = $this->createMock(BadgerInterface::class);
        $this->request->method('getParam')->with('badger_id')->willReturn(7);
        $this->repository->method('getById')->with(7)->willReturn($badger);

        $this->hydrator->method('toArray')->willReturn([
            BadgerInterface::BADGER_ID => 7,
            BadgerInterface::NAME => 'Sale',
            BadgerInterface::CONDITIONS_PAYLOAD => '{"type":"Combine"}',
            BadgerInterface::VISUALS => [['label_text' => 'x']],
            BadgerInterface::TOOLTIP => ['is_enabled' => '1', 'body_text' => 'tip', 'background_color' => '', 'text_color' => ''],
        ]);

        $data = $this->provider->getData();
        self::assertArrayHasKey(7, $data);
        self::assertArrayHasKey('visuals', $data[7]);
        self::assertArrayHasKey('tooltip', $data[7]);
        self::assertSame('1', $data[7]['tooltip']['is_enabled']);
    }

    public function testVisualsWrappedInContainer(): void
    {
        $badger = $this->createMock(BadgerInterface::class);
        $this->request->method('getParam')->willReturn(5);
        $this->repository->method('getById')->willReturn($badger);

        $this->hydrator->method('toArray')->willReturn([
            BadgerInterface::BADGER_ID => 5,
            BadgerInterface::VISUALS => [['surface' => 1]],
            BadgerInterface::TOOLTIP => null,
        ]);

        $data = $this->provider->getData();
        self::assertSame(
            ['visuals_container' => [['surface' => 1]]],
            $data[5]['visuals']
        );
    }

    public function testTooltipNullProducesDefaults(): void
    {
        $badger = $this->createMock(BadgerInterface::class);
        $this->request->method('getParam')->willReturn(2);
        $this->repository->method('getById')->willReturn($badger);

        $this->hydrator->method('toArray')->willReturn([
            BadgerInterface::BADGER_ID => 2,
            BadgerInterface::VISUALS => [],
            BadgerInterface::TOOLTIP => null,
        ]);

        $data = $this->provider->getData();
        self::assertSame('0', $data[2]['tooltip']['is_enabled']);
        self::assertSame('', $data[2]['tooltip']['body_text']);
    }

    public function testCachesLoadedData(): void
    {
        $badger = $this->createMock(BadgerInterface::class);
        $this->request->method('getParam')->willReturn(1);
        $this->repository->expects($this->once())->method('getById')->willReturn($badger);
        $this->hydrator->method('toArray')->willReturn([
            BadgerInterface::CONDITIONS_PAYLOAD => null,
            BadgerInterface::VISUALS => [],
            BadgerInterface::TOOLTIP => null,
        ]);

        $this->provider->getData();
        $this->provider->getData();
    }

    /**
     * Regression test: getData() must NOT overwrite $this->data entirely.
     * The injected $data['config'] (which carries submit_url from <submitUrl> XML)
     * must still be accessible via getConfigData() after getData() is called.
     * Before the fix, line "$this->data = [$id => $row]" wiped the config key,
     * making submit_url undefined in JS and causing all saves to POST to the edit URL.
     */
    public function testGetConfigDataSurvivesGetData(): void
    {
        $submitUrl = 'https://app.magento.test/admin/badger/badger/save/key/abc/';

        $collection = $this->createMock(Collection::class);
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $pool = $this->createMock(PoolInterface::class);
        $pool->method('getModifiersInstances')->willReturn([]);

        $providerWithConfig = new BadgerFormDataProvider(
            'iranimij_badger_form_data_source',
            'badger_id',
            'badger_id',
            $factory,
            $this->request,
            $this->repository,
            $this->hydrator,
            [],
            ['config' => ['submit_url' => $submitUrl]],
            $pool
        );

        $badger = $this->createMock(BadgerInterface::class);
        $this->request->method('getParam')->with('badger_id')->willReturn(9);
        $this->repository->method('getById')->with(9)->willReturn($badger);
        $this->hydrator->method('toArray')->willReturn([
            BadgerInterface::BADGER_ID => 9,
            BadgerInterface::VISUALS => [],
            BadgerInterface::TOOLTIP => null,
        ]);

        $providerWithConfig->getData();

        $configData = $providerWithConfig->getConfigData();
        self::assertArrayHasKey(
            'submit_url',
            $configData,
            'submit_url must survive getData() — if it is missing the form POSTs to the current page instead of the save action'
        );
        self::assertSame($submitUrl, $configData['submit_url']);
    }

    public function testGetConfigDataPreservedWhenNoId(): void
    {
        $submitUrl = 'https://app.magento.test/admin/badger/badger/save/key/xyz/';

        $collection = $this->createMock(Collection::class);
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $pool = $this->createMock(PoolInterface::class);
        $pool->method('getModifiersInstances')->willReturn([]);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->with('badger_id')->willReturn(0);

        $providerWithConfig = new BadgerFormDataProvider(
            'iranimij_badger_form_data_source',
            'badger_id',
            'badger_id',
            $factory,
            $request,
            $this->repository,
            $this->hydrator,
            [],
            ['config' => ['submit_url' => $submitUrl]],
            $pool
        );

        $providerWithConfig->getData();

        $configData = $providerWithConfig->getConfigData();
        self::assertSame($submitUrl, $configData['submit_url']);
    }
}
