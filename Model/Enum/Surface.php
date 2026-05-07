<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Enum;

enum Surface: int
{
    case CATEGORY_GRID = 0;
    case PRODUCT_PAGE = 1;
    case CART_CROSSSELL = 2;
    case RELATED = 3;
    case UPSELL = 4;

    public function code(): string
    {
        return strtolower($this->name);
    }
}
