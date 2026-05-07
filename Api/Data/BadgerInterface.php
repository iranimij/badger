<?php
declare(strict_types=1);

namespace Iranimij\Badger\Api\Data;

interface BadgerInterface
{
    public const BADGER_ID = 'badger_id';
    public const NAME = 'name';
    public const IS_ENABLED = 'is_enabled';
    public const PRIORITY = 'priority';
    public const IS_EXCLUSIVE = 'is_exclusive';
    public const USE_FOR_PARENT = 'use_for_parent';
    public const ACTIVE_FROM = 'active_from';
    public const ACTIVE_TO = 'active_to';
    public const CONDITIONS_PAYLOAD = 'conditions_payload';
    public const STORE_IDS = 'store_ids';
    public const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    public const VISUALS = 'visuals';
    public const TOOLTIP = 'tooltip';

    /**
     * @return int|null
     */
    public function getBadgerId(): ?int;

    /**
     * @param int|null $id
     * @return $this
     */
    public function setBadgerId(?int $id): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setIsEnabled(bool $enabled): self;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority): self;

    /**
     * @return bool
     */
    public function isExclusive(): bool;

    /**
     * @param bool $exclusive
     * @return $this
     */
    public function setIsExclusive(bool $exclusive): self;

    /**
     * @return bool
     */
    public function getUseForParent(): bool;

    /**
     * @param bool $useForParent
     * @return $this
     */
    public function setUseForParent(bool $useForParent): self;

    /**
     * @return string|null
     */
    public function getActiveFrom(): ?string;

    /**
     * @param string|null $activeFrom
     * @return $this
     */
    public function setActiveFrom(?string $activeFrom): self;

    /**
     * @return string|null
     */
    public function getActiveTo(): ?string;

    /**
     * @param string|null $activeTo
     * @return $this
     */
    public function setActiveTo(?string $activeTo): self;

    /**
     * @return string|null
     */
    public function getConditionsPayload(): ?string;

    /**
     * @param string|null $payload
     * @return $this
     */
    public function setConditionsPayload(?string $payload): self;

    /**
     * @return int[]
     */
    public function getStoreIds(): array;

    /**
     * @param int[] $storeIds
     * @return $this
     */
    public function setStoreIds(array $storeIds): self;

    /**
     * @return int[]
     */
    public function getCustomerGroupIds(): array;

    /**
     * @param int[] $ids
     * @return $this
     */
    public function setCustomerGroupIds(array $ids): self;

    /**
     * @return \Iranimij\Badger\Api\Data\VisualInterface[]
     */
    public function getVisuals(): array;

    /**
     * @param \Iranimij\Badger\Api\Data\VisualInterface[] $visuals
     * @return $this
     */
    public function setVisuals(array $visuals): self;

    /**
     * @return \Iranimij\Badger\Api\Data\TooltipInterface|null
     */
    public function getTooltip(): ?TooltipInterface;

    /**
     * @param \Iranimij\Badger\Api\Data\TooltipInterface|null $tooltip
     * @return $this
     */
    public function setTooltip(?TooltipInterface $tooltip): self;
}
