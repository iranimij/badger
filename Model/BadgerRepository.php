<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterface;
use Iranimij\Badger\Api\Data\BadgerSearchResultsInterfaceFactory;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Iranimij\Badger\Model\ResourceModel\Badger\Collection;
use Iranimij\Badger\Model\ResourceModel\Badger\CollectionFactory;
use Iranimij\Badger\Model\ResourceModel\CustomerGroupLink;
use Iranimij\Badger\Model\ResourceModel\StoreLink;
use Iranimij\Badger\Model\ResourceModel\Tooltip as TooltipResource;
use Iranimij\Badger\Model\ResourceModel\Visual as VisualResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

class BadgerRepository implements BadgerRepositoryInterface
{
    public function __construct(
        private readonly BadgerResource $badgerResource,
        private readonly CollectionFactory $collectionFactory,
        private readonly VisualResource $visualResource,
        private readonly TooltipResource $tooltipResource,
        private readonly StoreLink $storeLink,
        private readonly CustomerGroupLink $customerGroupLink,
        private readonly BadgerSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    public function save(BadgerInterface $badger): BadgerInterface
    {
        try {
            /** @var Badger $model */
            $model = $badger instanceof Badger
                ? $badger
                : $this->objectManager->create(Badger::class)->addData($this->flattenScalar($badger));

            $this->badgerResource->save($model);
            $badgerId = (int) $model->getBadgerId();

            $this->storeLink->sync($badgerId, $badger->getStoreIds());
            $this->customerGroupLink->sync($badgerId, $badger->getCustomerGroupIds());

            $this->syncVisuals($badgerId, $badger->getVisuals());
            $this->syncTooltip($badgerId, $badger->getTooltip());

            $badger->setBadgerId($badgerId);
            return $this->getById($badgerId);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save badger: %1', $e->getMessage()), $e);
        }
    }

    public function getById(int $badgerId): BadgerInterface
    {
        /** @var Badger $badger */
        $badger = $this->objectManager->create(Badger::class);
        $this->badgerResource->load($badger, $badgerId);
        if (!$badger->getBadgerId()) {
            throw new NoSuchEntityException(__('Badger with ID "%1" does not exist.', $badgerId));
        }

        $badger->setStoreIds($this->storeLink->fetch($badgerId));
        $badger->setCustomerGroupIds($this->customerGroupLink->fetch($badgerId));
        $badger->setVisuals($this->loadVisuals($badgerId));
        $badger->setTooltip($this->loadTooltip($badgerId));

        return $badger;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): BadgerSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var BadgerSearchResultsInterface $results */
        $results = $this->searchResultsFactory->create();
        $results->setSearchCriteria($searchCriteria);
        $items = [];
        foreach ($collection->getItems() as $item) {
            $items[] = $this->getById((int) $item->getData(BadgerInterface::BADGER_ID));
        }
        $results->setItems($items);
        $results->setTotalCount($collection->getSize());
        return $results;
    }

    public function deleteById(int $badgerId): bool
    {
        $badger = $this->getById($badgerId);
        try {
            /** @var Badger $model */
            $model = $this->objectManager->create(Badger::class);
            $model->setBadgerId($badgerId);
            $this->badgerResource->delete($model);
            return true;
        } catch (\Throwable $e) {
            throw new CouldNotDeleteException(__('Could not delete badger: %1', $e->getMessage()), $e);
        }
    }

    public function duplicate(int $badgerId): BadgerInterface
    {
        $source = $this->getById($badgerId);
        /** @var Badger $copy */
        $copy = $this->objectManager->create(Badger::class);
        $copy->addData($this->flattenScalar($source));
        $copy->setBadgerId(null);
        $copy->setIsEnabled(false);
        $copy->setName(__('Copy of %1', $source->getName())->render());
        $copy->setStoreIds($source->getStoreIds());
        $copy->setCustomerGroupIds($source->getCustomerGroupIds());
        $copy->setVisuals($source->getVisuals());
        $copy->setTooltip($source->getTooltip());
        return $this->save($copy);
    }

    private function flattenScalar(BadgerInterface $badger): array
    {
        return [
            BadgerInterface::BADGER_ID => $badger->getBadgerId(),
            BadgerInterface::NAME => $badger->getName(),
            BadgerInterface::IS_ENABLED => $badger->isEnabled() ? 1 : 0,
            BadgerInterface::PRIORITY => $badger->getPriority(),
            BadgerInterface::IS_EXCLUSIVE => $badger->isExclusive() ? 1 : 0,
            BadgerInterface::USE_FOR_PARENT => $badger->getUseForParent() ? 1 : 0,
            BadgerInterface::ACTIVE_FROM => $badger->getActiveFrom(),
            BadgerInterface::ACTIVE_TO => $badger->getActiveTo(),
            BadgerInterface::CONDITIONS_PAYLOAD => $badger->getConditionsPayload(),
        ];
    }

    private function syncVisuals(int $badgerId, array $visuals): void
    {
        $this->visualResource->deleteForBadger($badgerId);
        foreach ($visuals as $visual) {
            if (!$visual instanceof VisualInterface) {
                continue;
            }
            $this->visualResource->saveRow([
                VisualInterface::BADGER_ID => $badgerId,
                VisualInterface::SURFACE => $visual->getSurface(),
                VisualInterface::SHAPE_KIND => $visual->getShapeKind(),
                VisualInterface::PLACEMENT => $visual->getPlacement(),
                VisualInterface::LABEL_TEXT => $visual->getLabelText(),
                VisualInterface::IMAGE_PATH => $visual->getImagePath(),
                VisualInterface::REDIRECT_URL => $visual->getRedirectUrl(),
                VisualInterface::ALT_TEXT => $visual->getAltText(),
                VisualInterface::CSS_CLASS => $visual->getCssClass(),
                VisualInterface::SIZE_PERCENT => $visual->getSizePercent(),
                VisualInterface::STYLE_PAYLOAD => $visual->getStylePayload(),
            ]);
        }
    }

    private function syncTooltip(int $badgerId, ?TooltipInterface $tooltip): void
    {
        if ($tooltip === null) {
            $this->tooltipResource->deleteForBadger($badgerId);
            return;
        }
        $this->tooltipResource->saveRow([
            TooltipInterface::BADGER_ID => $badgerId,
            TooltipInterface::IS_ENABLED => $tooltip->isEnabled() ? 1 : 0,
            TooltipInterface::BODY_TEXT => $tooltip->getBodyText(),
            TooltipInterface::BACKGROUND_COLOR => $tooltip->getBackgroundColor(),
            TooltipInterface::TEXT_COLOR => $tooltip->getTextColor(),
        ]);
    }

    private function loadVisuals(int $badgerId): array
    {
        $visuals = [];
        foreach ($this->visualResource->fetchForBadger($badgerId) as $row) {
            /** @var VisualInterface $visual */
            $visual = $this->objectManager->create(VisualInterface::class);
            $visual->setBadgerId((int) $row[VisualInterface::BADGER_ID]);
            $visual->setSurface((int) $row[VisualInterface::SURFACE]);
            $visual->setShapeKind((int) $row[VisualInterface::SHAPE_KIND]);
            $visual->setPlacement((int) $row[VisualInterface::PLACEMENT]);
            $visual->setLabelText($row[VisualInterface::LABEL_TEXT] ?? null);
            $visual->setImagePath($row[VisualInterface::IMAGE_PATH] ?? null);
            $visual->setRedirectUrl($row[VisualInterface::REDIRECT_URL] ?? null);
            $visual->setAltText($row[VisualInterface::ALT_TEXT] ?? null);
            $visual->setCssClass($row[VisualInterface::CSS_CLASS] ?? null);
            $visual->setSizePercent((int) ($row[VisualInterface::SIZE_PERCENT] ?? 25));
            $visual->setStylePayload($row[VisualInterface::STYLE_PAYLOAD] ?? null);
            $visuals[] = $visual;
        }
        return $visuals;
    }

    private function loadTooltip(int $badgerId): ?TooltipInterface
    {
        $row = $this->tooltipResource->loadByBadger($badgerId);
        if ($row === null) {
            return null;
        }
        /** @var TooltipInterface $tooltip */
        $tooltip = $this->objectManager->create(TooltipInterface::class);
        $tooltip->setBadgerId((int) $row[TooltipInterface::BADGER_ID]);
        $tooltip->setIsEnabled((bool) $row[TooltipInterface::IS_ENABLED]);
        $tooltip->setBodyText($row[TooltipInterface::BODY_TEXT] ?? null);
        $tooltip->setBackgroundColor($row[TooltipInterface::BACKGROUND_COLOR] ?? null);
        $tooltip->setTextColor($row[TooltipInterface::TEXT_COLOR] ?? null);
        return $tooltip;
    }
}
