<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Integration\Repository;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BadgerRepositoryTest extends TestCase
{
    private BadgerRepositoryInterface $repository;
    private SearchCriteriaBuilder $criteriaBuilder;

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->repository = $om->get(BadgerRepositoryInterface::class);
        $this->criteriaBuilder = $om->get(SearchCriteriaBuilder::class);
    }

    public function testSaveAndGetById(): void
    {
        $badger = $this->makeBadger('Test Badge', true, 10);
        $saved = $this->repository->save($badger);

        self::assertNotNull($saved->getBadgerId());
        self::assertSame('Test Badge', $saved->getName());
        self::assertTrue($saved->isEnabled());
        self::assertSame(10, $saved->getPriority());
    }

    public function testGetByIdThrowsForMissing(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById(999999);
    }

    public function testSaveWithStoreIds(): void
    {
        $badger = $this->makeBadger('Scoped Badge');
        $badger->setStoreIds([0, 1]);

        $saved = $this->repository->save($badger);
        $loaded = $this->repository->getById((int) $saved->getBadgerId());

        self::assertEqualsCanonicalizing([0, 1], $loaded->getStoreIds());
    }

    public function testSaveWithCustomerGroupIds(): void
    {
        $badger = $this->makeBadger('Group Badge');
        $badger->setCustomerGroupIds([0, 1, 2]);

        $saved = $this->repository->save($badger);
        $loaded = $this->repository->getById((int) $saved->getBadgerId());

        self::assertEqualsCanonicalizing([0, 1, 2], $loaded->getCustomerGroupIds());
    }

    public function testSaveWithTooltip(): void
    {
        $om = Bootstrap::getObjectManager();
        $tooltip = $om->create(TooltipInterface::class);
        $tooltip->setIsEnabled(true);
        $tooltip->setBodyText('Hover text');
        $tooltip->setBackgroundColor('#333333');
        $tooltip->setTextColor('#ffffff');

        $badger = $this->makeBadger('Tooltip Badge');
        $badger->setTooltip($tooltip);

        $saved = $this->repository->save($badger);
        $loaded = $this->repository->getById((int) $saved->getBadgerId());

        self::assertNotNull($loaded->getTooltip());
        self::assertTrue($loaded->getTooltip()->isEnabled());
        self::assertSame('Hover text', $loaded->getTooltip()->getBodyText());
        self::assertSame('#333333', $loaded->getTooltip()->getBackgroundColor());
        self::assertSame('#ffffff', $loaded->getTooltip()->getTextColor());
    }

    public function testSaveWithVisuals(): void
    {
        $om = Bootstrap::getObjectManager();
        $visual = $om->create(VisualInterface::class);
        $visual->setSurface(1);
        $visual->setShapeKind(1);
        $visual->setPlacement(1);
        $visual->setLabelText('{{discount_percent}}% OFF');
        $visual->setSizePercent(25);

        $badger = $this->makeBadger('Visual Badge');
        $badger->setVisuals([$visual]);

        $saved = $this->repository->save($badger);
        $loaded = $this->repository->getById((int) $saved->getBadgerId());

        self::assertCount(1, $loaded->getVisuals());
        self::assertSame('{{discount_percent}}% OFF', $loaded->getVisuals()[0]->getLabelText());
    }

    public function testUpdateBadger(): void
    {
        $badger = $this->makeBadger('Original Name');
        $saved = $this->repository->save($badger);

        $saved->setName('Updated Name');
        $saved->setPriority(99);
        $updated = $this->repository->save($saved);

        $loaded = $this->repository->getById((int) $updated->getBadgerId());
        self::assertSame('Updated Name', $loaded->getName());
        self::assertSame(99, $loaded->getPriority());
    }

    public function testDeleteById(): void
    {
        $badger = $this->makeBadger('To Delete');
        $saved = $this->repository->save($badger);
        $id = (int) $saved->getBadgerId();

        $result = $this->repository->deleteById($id);
        self::assertTrue($result);

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($id);
    }

    public function testGetListReturnsAllBadgers(): void
    {
        $this->repository->save($this->makeBadger('Alpha'));
        $this->repository->save($this->makeBadger('Beta'));
        $this->repository->save($this->makeBadger('Gamma'));

        $criteria = $this->criteriaBuilder->create();
        $results = $this->repository->getList($criteria);

        self::assertGreaterThanOrEqual(3, $results->getTotalCount());
    }

    public function testGetListWithNameFilter(): void
    {
        $uniqueName = 'FilterMe-' . uniqid();
        $this->repository->save($this->makeBadger($uniqueName));
        $this->repository->save($this->makeBadger('LeaveMe-' . uniqid()));

        $criteria = $this->criteriaBuilder
            ->addFilter(BadgerInterface::NAME, $uniqueName)
            ->create();
        $results = $this->repository->getList($criteria);

        self::assertSame(1, $results->getTotalCount());
        self::assertSame($uniqueName, $results->getItems()[0]->getName());
    }

    public function testGetListWithPaging(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->save($this->makeBadger("Paged {$i}"));
        }

        $criteria = $this->criteriaBuilder->create();
        $criteria->setPageSize(2)->setCurrentPage(1);
        $results = $this->repository->getList($criteria);

        self::assertCount(2, $results->getItems());
        self::assertGreaterThanOrEqual(5, $results->getTotalCount());
    }

    public function testSaveConditionsPayload(): void
    {
        $payload = json_encode([
            'op' => 'AND',
            'children' => [['token' => 'on_sale', 'args' => []]],
        ]);

        $badger = $this->makeBadger('Condition Badge');
        $badger->setConditionsPayload($payload);

        $saved = $this->repository->save($badger);
        $loaded = $this->repository->getById((int) $saved->getBadgerId());

        self::assertSame($payload, $loaded->getConditionsPayload());
    }

    public function testSaveSchedule(): void
    {
        $from = '2026-01-01 00:00:00';
        $to = '2026-12-31 23:59:59';

        $badger = $this->makeBadger('Scheduled Badge');
        $badger->setActiveFrom($from);
        $badger->setActiveTo($to);

        $saved = $this->repository->save($badger);
        $loaded = $this->repository->getById((int) $saved->getBadgerId());

        self::assertSame($from, $loaded->getActiveFrom());
        self::assertSame($to, $loaded->getActiveTo());
    }

    public function testTooltipRemovedOnUpdate(): void
    {
        $om = Bootstrap::getObjectManager();
        $tooltip = $om->create(TooltipInterface::class);
        $tooltip->setIsEnabled(true);
        $tooltip->setBodyText('Will be removed');

        $badger = $this->makeBadger('Tooltip Remove Test');
        $badger->setTooltip($tooltip);
        $saved = $this->repository->save($badger);

        $saved->setTooltip(null);
        $this->repository->save($saved);

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertNull($loaded->getTooltip());
    }

    public function testVisualsReplacedOnUpdate(): void
    {
        $om = Bootstrap::getObjectManager();

        $v1 = $om->create(VisualInterface::class);
        $v1->setSurface(1)->setShapeKind(1)->setPlacement(1)->setLabelText('First');

        $badger = $this->makeBadger('Visual Replace Test');
        $badger->setVisuals([$v1]);
        $saved = $this->repository->save($badger);

        $v2 = $om->create(VisualInterface::class);
        $v2->setSurface(2)->setShapeKind(1)->setPlacement(2)->setLabelText('Second');

        $saved->setVisuals([$v2]);
        $this->repository->save($saved);

        $loaded = $this->repository->getById((int) $saved->getBadgerId());
        self::assertCount(1, $loaded->getVisuals());
        self::assertSame('Second', $loaded->getVisuals()[0]->getLabelText());
    }

    private function makeBadger(string $name, bool $enabled = false, int $priority = 0): BadgerInterface
    {
        $badger = Bootstrap::getObjectManager()->create(BadgerInterface::class);
        $badger->setName($name);
        $badger->setIsEnabled($enabled);
        $badger->setPriority($priority);
        $badger->setIsExclusive(false);
        $badger->setUseForParent(false);
        $badger->setStoreIds([]);
        $badger->setCustomerGroupIds([]);
        $badger->setVisuals([]);
        return $badger;
    }
}
