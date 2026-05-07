<?php
declare(strict_types=1);

namespace Iranimij\Badger\Setup\Patch\Data;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DeployExampleBadgers implements DataPatchInterface
{
    public function __construct(
        private readonly BadgerRepositoryInterface $repository,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->createOnSaleBadger();
        $this->createNewBadger();
        return $this;
    }

    private function createOnSaleBadger(): void
    {
        try {
            /** @var BadgerInterface $badger */
            $badger = $this->objectManager->create(BadgerInterface::class);
            $badger->setName('Sale');
            $badger->setIsEnabled(true);
            $badger->setPriority(10);
            $badger->setIsExclusive(false);
            $badger->setUseForParent(false);
            $badger->setStoreIds([]);
            $badger->setCustomerGroupIds([]);
            $badger->setConditionsPayload('{"op":"and","children":[{"token":"on_sale","args":{}}]}');

            $visual = $this->objectManager->create(VisualInterface::class);
            $visual->setSurface(Surface::CATEGORY_GRID->value);
            $visual->setShapeKind(ShapeKind::ROUNDED_RECT->value);
            $visual->setPlacement(Placement::TOP_LEFT->value);
            $visual->setLabelText('SALE');
            $visual->setSizePercent(20);
            $visual->setStylePayload('{"bg_color":"#e12b2b","text_color":"#ffffff"}');

            $badger->setVisuals([$visual]);
            $badger->setTooltip(null);

            $this->repository->save($badger);
        } catch (CouldNotSaveException) {
            // skip if already exists or DB not ready
        }
    }

    private function createNewBadger(): void
    {
        try {
            /** @var BadgerInterface $badger */
            $badger = $this->objectManager->create(BadgerInterface::class);
            $badger->setName('New');
            $badger->setIsEnabled(true);
            $badger->setPriority(5);
            $badger->setIsExclusive(false);
            $badger->setUseForParent(false);
            $badger->setStoreIds([]);
            $badger->setCustomerGroupIds([]);
            $badger->setConditionsPayload('{"op":"and","children":[{"token":"is_new","args":{}}]}');

            $visual = $this->objectManager->create(VisualInterface::class);
            $visual->setSurface(Surface::CATEGORY_GRID->value);
            $visual->setShapeKind(ShapeKind::ROUNDED_RECT->value);
            $visual->setPlacement(Placement::TOP_RIGHT->value);
            $visual->setLabelText('NEW');
            $visual->setSizePercent(20);
            $visual->setStylePayload('{"bg_color":"#2b8be1","text_color":"#ffffff"}');

            $badger->setVisuals([$visual]);
            $badger->setTooltip(null);

            $this->repository->save($badger);
        } catch (CouldNotSaveException) {
            // skip if already exists or DB not ready
        }
    }
}
