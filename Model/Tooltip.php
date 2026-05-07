<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Api\Data\TooltipInterface;
use Iranimij\Badger\Model\ResourceModel\Tooltip as TooltipResource;
use Magento\Framework\Model\AbstractModel;

class Tooltip extends AbstractModel implements TooltipInterface
{
    protected function _construct(): void
    {
        $this->_init(TooltipResource::class);
    }

    public function getBadgerId(): ?int
    {
        $v = $this->getData(self::BADGER_ID);
        return $v === null ? null : (int) $v;
    }

    public function setBadgerId(?int $id): self
    {
        return $this->setData(self::BADGER_ID, $id);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getData(self::IS_ENABLED);
    }

    public function setIsEnabled(bool $enabled): self
    {
        return $this->setData(self::IS_ENABLED, $enabled ? 1 : 0);
    }

    public function getBodyText(): ?string
    {
        $v = $this->getData(self::BODY_TEXT);
        return $v === null ? null : (string) $v;
    }

    public function setBodyText(?string $text): self
    {
        return $this->setData(self::BODY_TEXT, $text);
    }

    public function getBackgroundColor(): ?string
    {
        $v = $this->getData(self::BACKGROUND_COLOR);
        return $v === null ? null : (string) $v;
    }

    public function setBackgroundColor(?string $color): self
    {
        return $this->setData(self::BACKGROUND_COLOR, $color);
    }

    public function getTextColor(): ?string
    {
        $v = $this->getData(self::TEXT_COLOR);
        return $v === null ? null : (string) $v;
    }

    public function setTextColor(?string $color): self
    {
        return $this->setData(self::TEXT_COLOR, $color);
    }
}
