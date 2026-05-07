<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\ReadModel;

use Iranimij\Badger\Model\Enum\Placement;
use Iranimij\Badger\Model\Enum\ShapeKind;
use Iranimij\Badger\Model\Enum\Surface;

final class ResolvedBadger
{
    public function __construct(
        public readonly int $badgerId,
        public readonly string $name,
        public readonly int $priority,
        public readonly bool $isExclusive,
        public readonly Surface $surface,
        public readonly Placement $placement,
        public readonly ShapeKind $shape,
        public readonly string $labelText,
        public readonly ?string $imagePath,
        public readonly ?string $redirectUrl,
        public readonly ?string $altText,
        public readonly ?string $cssClass,
        public readonly int $sizePercent,
        public readonly ?string $badgeBgColor,
        public readonly ?string $badgeTextColor,
        public readonly ?string $tooltipText,
        public readonly ?string $tooltipBgColor,
        public readonly ?string $tooltipTextColor
    ) {
    }

    public function hasTooltip(): bool
    {
        return $this->tooltipText !== null && $this->tooltipText !== '';
    }
}
