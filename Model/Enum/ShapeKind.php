<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Enum;

enum ShapeKind: int
{
    case TEXT = 0;
    case IMAGE = 1;
    case ROUNDED_RECT = 2;
    case CIRCLE = 3;
    case RIBBON = 4;

    public function rendersImage(): bool
    {
        return match ($this) {
            self::TEXT => false,
            default => true,
        };
    }
}
