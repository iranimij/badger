<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Enum;

enum Placement: int
{
    case TOP_LEFT = 0;
    case TOP_CENTER = 1;
    case TOP_RIGHT = 2;
    case MIDDLE_LEFT = 3;
    case MIDDLE_CENTER = 4;
    case MIDDLE_RIGHT = 5;
    case BOTTOM_LEFT = 6;
    case BOTTOM_CENTER = 7;
    case BOTTOM_RIGHT = 8;

    public function cssClass(): string
    {
        return 'iranimij-badger--' . strtolower(str_replace('_', '-', $this->name));
    }
}
