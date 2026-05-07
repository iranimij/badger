<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Cron;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Cron\ActivateScheduled;
use Iranimij\Badger\Cron\DeactivateExpired;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ScheduleTest extends TestCase
{
    private BadgerRepositoryInterface $repository;
    private ActivateScheduled $activateCron;
    private DeactivateExpired $deactivateCron;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->repository = $om->get(BadgerRepositoryInterface::class);
        $this->activateCron = $om->get(ActivateScheduled::class);
        $this->deactivateCron = $om->get(DeactivateExpired::class);
    }

    public function testActivatesCronEnablesScheduledBadger(): void
    {
        $badger = $this->makeBadger('Scheduled Badge', false);
        $badger->setActiveFrom(date('Y-m-d H:i:s', strtotime('-1 hour')));
        $badger->setActiveTo(date('Y-m-d H:i:s', strtotime('+1 hour')));
        $saved = $this->repository->save($badger);

        $this->activateCron->execute();

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertTrue($loaded->isEnabled(), 'Cron should have enabled the badger whose active_from has passed');
    }

    public function testActivateCronDoesNotActivateFutureBadger(): void
    {
        $badger = $this->makeBadger('Future Badge', false);
        $badger->setActiveFrom(date('Y-m-d H:i:s', strtotime('+2 hours')));
        $badger->setActiveTo(date('Y-m-d H:i:s', strtotime('+4 hours')));
        $saved = $this->repository->save($badger);

        $this->activateCron->execute();

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertFalse($loaded->isEnabled(), 'Future badge should remain disabled');
    }

    public function testDeactivateCronDisablesExpiredBadger(): void
    {
        $badger = $this->makeBadger('Expired Badge', true);
        $badger->setActiveFrom(date('Y-m-d H:i:s', strtotime('-4 hours')));
        $badger->setActiveTo(date('Y-m-d H:i:s', strtotime('-1 hour')));
        $saved = $this->repository->save($badger);

        $this->deactivateCron->execute();

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertFalse($loaded->isEnabled(), 'Expired badge should be disabled by cron');
    }

    public function testDeactivateCronKeepsActiveCurrentBadge(): void
    {
        $badger = $this->makeBadger('Active Badge', true);
        $badger->setActiveFrom(date('Y-m-d H:i:s', strtotime('-1 hour')));
        $badger->setActiveTo(date('Y-m-d H:i:s', strtotime('+1 hour')));
        $saved = $this->repository->save($badger);

        $this->deactivateCron->execute();

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertTrue($loaded->isEnabled(), 'Currently active badge should remain enabled');
    }

    public function testBadgerWithNoScheduleUnaffectedByActivateCron(): void
    {
        $badger = $this->makeBadger('No Schedule', false);
        // no active_from / active_to
        $saved = $this->repository->save($badger);

        $this->activateCron->execute();

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertFalse($loaded->isEnabled(), 'Badge with no schedule should not be enabled by cron');
    }

    private function makeBadger(string $name, bool $enabled): BadgerInterface
    {
        $badger = Bootstrap::getObjectManager()->create(BadgerInterface::class);
        $badger->setName($name);
        $badger->setIsEnabled($enabled);
        $badger->setPriority(0);
        $badger->setIsExclusive(false);
        $badger->setUseForParent(false);
        $badger->setStoreIds([]);
        $badger->setCustomerGroupIds([]);
        $badger->setVisuals([]);
        return $badger;
    }
}
