<?php
declare(strict_types=1);

namespace Iranimij\Badger\Api\Data;

interface TooltipInterface
{
    public const BADGER_ID = 'badger_id';
    public const IS_ENABLED = 'is_enabled';
    public const BODY_TEXT = 'body_text';
    public const BACKGROUND_COLOR = 'background_color';
    public const TEXT_COLOR = 'text_color';

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
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setIsEnabled(bool $enabled): self;

    /**
     * @return string|null
     */
    public function getBodyText(): ?string;

    /**
     * @param string|null $text
     * @return $this
     */
    public function setBodyText(?string $text): self;

    /**
     * @return string|null
     */
    public function getBackgroundColor(): ?string;

    /**
     * @param string|null $color
     * @return $this
     */
    public function setBackgroundColor(?string $color): self;

    /**
     * @return string|null
     */
    public function getTextColor(): ?string;

    /**
     * @param string|null $color
     * @return $this
     */
    public function setTextColor(?string $color): self;
}
