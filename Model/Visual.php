<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model;

use Iranimij\Badger\Api\Data\VisualInterface;
use Iranimij\Badger\Model\ResourceModel\Visual as VisualResource;
use Magento\Framework\Model\AbstractModel;

class Visual extends AbstractModel implements VisualInterface
{
    protected function _construct(): void
    {
        $this->_init(VisualResource::class);
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

    public function getSurface(): int
    {
        return (int) $this->getData(self::SURFACE);
    }

    public function setSurface(int $surface): self
    {
        return $this->setData(self::SURFACE, $surface);
    }

    public function getShapeKind(): int
    {
        return (int) $this->getData(self::SHAPE_KIND);
    }

    public function setShapeKind(int $kind): self
    {
        return $this->setData(self::SHAPE_KIND, $kind);
    }

    public function getPlacement(): int
    {
        return (int) $this->getData(self::PLACEMENT);
    }

    public function setPlacement(int $placement): self
    {
        return $this->setData(self::PLACEMENT, $placement);
    }

    public function getLabelText(): ?string
    {
        $v = $this->getData(self::LABEL_TEXT);
        return $v === null ? null : (string) $v;
    }

    public function setLabelText(?string $text): self
    {
        return $this->setData(self::LABEL_TEXT, $text);
    }

    public function getImagePath(): ?string
    {
        $v = $this->getData(self::IMAGE_PATH);
        return $v === null ? null : (string) $v;
    }

    public function setImagePath(?string $path): self
    {
        return $this->setData(self::IMAGE_PATH, $path);
    }

    public function getRedirectUrl(): ?string
    {
        $v = $this->getData(self::REDIRECT_URL);
        return $v === null ? null : (string) $v;
    }

    public function setRedirectUrl(?string $url): self
    {
        return $this->setData(self::REDIRECT_URL, $url);
    }

    public function getAltText(): ?string
    {
        $v = $this->getData(self::ALT_TEXT);
        return $v === null ? null : (string) $v;
    }

    public function setAltText(?string $text): self
    {
        return $this->setData(self::ALT_TEXT, $text);
    }

    public function getSizePercent(): int
    {
        return (int) $this->getData(self::SIZE_PERCENT);
    }

    public function setSizePercent(int $percent): self
    {
        return $this->setData(self::SIZE_PERCENT, $percent);
    }

    public function getStylePayload(): ?string
    {
        $v = $this->getData(self::STYLE_PAYLOAD);
        return $v === null ? null : (string) $v;
    }

    public function setStylePayload(?string $payload): self
    {
        return $this->setData(self::STYLE_PAYLOAD, $payload);
    }

    public function getCssClass(): ?string
    {
        $v = $this->getData(self::CSS_CLASS);
        return $v === null ? null : (string) $v;
    }

    public function setCssClass(?string $cssClass): self
    {
        return $this->setData(self::CSS_CLASS, $cssClass);
    }
}
