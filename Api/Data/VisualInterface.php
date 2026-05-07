<?php
declare(strict_types=1);

namespace Iranimij\Badger\Api\Data;

interface VisualInterface
{
    public const BADGER_ID = 'badger_id';
    public const SURFACE = 'surface';
    public const SHAPE_KIND = 'shape_kind';
    public const PLACEMENT = 'placement';
    public const LABEL_TEXT = 'label_text';
    public const IMAGE_PATH = 'image_path';
    public const REDIRECT_URL = 'redirect_url';
    public const ALT_TEXT = 'alt_text';
    public const SIZE_PERCENT = 'size_percent';
    public const STYLE_PAYLOAD = 'style_payload';
    public const CSS_CLASS = 'css_class';

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
     * @return int
     */
    public function getSurface(): int;

    /**
     * @param int $surface
     * @return $this
     */
    public function setSurface(int $surface): self;

    /**
     * @return int
     */
    public function getShapeKind(): int;

    /**
     * @param int $kind
     * @return $this
     */
    public function setShapeKind(int $kind): self;

    /**
     * @return int
     */
    public function getPlacement(): int;

    /**
     * @param int $placement
     * @return $this
     */
    public function setPlacement(int $placement): self;

    /**
     * @return string|null
     */
    public function getLabelText(): ?string;

    /**
     * @param string|null $text
     * @return $this
     */
    public function setLabelText(?string $text): self;

    /**
     * @return string|null
     */
    public function getImagePath(): ?string;

    /**
     * @param string|null $path
     * @return $this
     */
    public function setImagePath(?string $path): self;

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string;

    /**
     * @param string|null $url
     * @return $this
     */
    public function setRedirectUrl(?string $url): self;

    /**
     * @return string|null
     */
    public function getAltText(): ?string;

    /**
     * @param string|null $text
     * @return $this
     */
    public function setAltText(?string $text): self;

    /**
     * @return int
     */
    public function getSizePercent(): int;

    /**
     * @param int $percent
     * @return $this
     */
    public function setSizePercent(int $percent): self;

    /**
     * @return string|null
     */
    public function getStylePayload(): ?string;

    /**
     * @param string|null $payload
     * @return $this
     */
    public function setStylePayload(?string $payload): self;

    /**
     * @return string|null
     */
    public function getCssClass(): ?string;

    /**
     * @param string|null $cssClass
     * @return $this
     */
    public function setCssClass(?string $cssClass): self;
}
