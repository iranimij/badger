<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Api\Data\BadgerInterface;
use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\ResourceModel\Badger as BadgerResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Badger extends AbstractModel implements BadgerInterface, IdentityInterface
{
    public const CACHE_TAG = 'iranimij_badger';

    protected $_eventPrefix = 'iranimij_badger';
    protected $_eventObject = 'badger';
    protected $_idFieldName = 'badger_id';

    protected function _construct(): void
    {
        $this->_init(BadgerResource::class);
    }

    public function getIdentities(): array
    {
        $id = $this->getBadgerId();
        return $id === null ? [self::CACHE_TAG] : [self::CACHE_TAG . '_' . $id];
    }

    public function getBadgerId(): ?int
    {
        $value = $this->getData(self::BADGER_ID);
        return $value === null ? null : (int) $value;
    }

    public function setBadgerId(?int $id): self
    {
        return $this->setData(self::BADGER_ID, $id);
    }

    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getData(self::IS_ENABLED);
    }

    public function setIsEnabled(bool $enabled): self
    {
        return $this->setData(self::IS_ENABLED, $enabled ? 1 : 0);
    }

    public function getPriority(): int
    {
        return (int) $this->getData(self::PRIORITY);
    }

    public function setPriority(int $priority): self
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    public function isExclusive(): bool
    {
        return (bool) $this->getData(self::IS_EXCLUSIVE);
    }

    public function setIsExclusive(bool $exclusive): self
    {
        return $this->setData(self::IS_EXCLUSIVE, $exclusive ? 1 : 0);
    }

    public function getUseForParent(): bool
    {
        return (bool) $this->getData(self::USE_FOR_PARENT);
    }

    public function setUseForParent(bool $useForParent): self
    {
        return $this->setData(self::USE_FOR_PARENT, $useForParent ? 1 : 0);
    }

    public function getActiveFrom(): ?string
    {
        $value = $this->getData(self::ACTIVE_FROM);
        return $value === null ? null : (string) $value;
    }

    public function setActiveFrom(?string $activeFrom): self
    {
        return $this->setData(self::ACTIVE_FROM, $activeFrom);
    }

    public function getActiveTo(): ?string
    {
        $value = $this->getData(self::ACTIVE_TO);
        return $value === null ? null : (string) $value;
    }

    public function setActiveTo(?string $activeTo): self
    {
        return $this->setData(self::ACTIVE_TO, $activeTo);
    }

    public function getConditionsPayload(): ?string
    {
        $value = $this->getData(self::CONDITIONS_PAYLOAD);
        return $value === null ? null : (string) $value;
    }

    public function setConditionsPayload(?string $payload): self
    {
        return $this->setData(self::CONDITIONS_PAYLOAD, $payload);
    }

    public function getStoreIds(): array
    {
        $raw = $this->getData(self::STORE_IDS);
        return is_array($raw) ? array_map('intval', $raw) : [];
    }

    public function setStoreIds(array $storeIds): self
    {
        return $this->setData(self::STORE_IDS, array_values(array_map('intval', $storeIds)));
    }

    public function getCustomerGroupIds(): array
    {
        $raw = $this->getData(self::CUSTOMER_GROUP_IDS);
        return is_array($raw) ? array_map('intval', $raw) : [];
    }

    public function setCustomerGroupIds(array $ids): self
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, array_values(array_map('intval', $ids)));
    }

    public function getVisuals(): array
    {
        $raw = $this->getData(self::VISUALS);
        return is_array($raw) ? array_values($raw) : [];
    }

    public function setVisuals(array $visuals): self
    {
        return $this->setData(self::VISUALS, array_values($visuals));
    }

    public function getTooltip(): ?TooltipInterface
    {
        $value = $this->getData(self::TOOLTIP);
        return $value instanceof TooltipInterface ? $value : null;
    }

    public function setTooltip(?TooltipInterface $tooltip): self
    {
        return $this->setData(self::TOOLTIP, $tooltip);
    }
}
