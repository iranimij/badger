<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Config;

use Iranimij\Core\Model\Config\ConfigProviderAbstract;

class BadgerConfigProvider extends ConfigProviderAbstract
{
    // General
    public const PATH_ENABLED          = 'iranimij_badger/general/enabled';
    public const PATH_NEW_FOR_DAYS     = 'iranimij_badger/general/new_for_days';

    // Display
    public const PATH_MAX_LABELS           = 'iranimij_badger/display/max_labels';
    public const PATH_SHOW_SEVERAL         = 'iranimij_badger/display/show_several_on_place';
    public const PATH_LABELS_ALIGNMENT     = 'iranimij_badger/display/labels_alignment';
    public const PATH_MARGIN_BETWEEN       = 'iranimij_badger/display/margin_between';
    public const PATH_HIDE_IF_ZERO         = 'iranimij_badger/display/hide_if_zero_label';

    // Selectors
    public const PATH_SEL_CATEGORY        = 'iranimij_badger/selectors/category_image';
    public const PATH_SEL_PRODUCT_PAGE    = 'iranimij_badger/selectors/product_page_image';

    // On Sale
    public const PATH_SALE_MIN            = 'iranimij_badger/on_sale/sale_min';
    public const PATH_SALE_MIN_PERCENT    = 'iranimij_badger/on_sale/sale_min_percent';
    public const PATH_SALE_ROUNDING       = 'iranimij_badger/on_sale/rounding';

    // Is New
    public const PATH_NEW_USE_DATES       = 'iranimij_badger/new/use_new_dates';
    public const PATH_NEW_USE_CREATION    = 'iranimij_badger/new/use_creation_date';

    // Stock Status
    public const PATH_STOCK_OOS_ONLY      = 'iranimij_badger/stock_status/out_of_stock_only';
    public const PATH_STOCK_DEFAULT_LABEL = 'iranimij_badger/stock_status/default_label';

    // Tooltip
    public const PATH_TOOLTIP_ENABLED     = 'iranimij_badger/tooltip/enabled';
    public const PATH_TOOLTIP_BG          = 'iranimij_badger/tooltip/background_color';
    public const PATH_TOOLTIP_TEXT_COLOR  = 'iranimij_badger/tooltip/text_color';

    // ── General ────────────────────────────────────────────────────────────
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_ENABLED, $storeId);
    }

    public function getNewForDays(?int $storeId = null): int
    {
        return $this->readInt(self::PATH_NEW_FOR_DAYS, $storeId, 14);
    }

    // ── Display ────────────────────────────────────────────────────────────
    public function getMaxLabels(?int $storeId = null): int
    {
        return $this->readInt(self::PATH_MAX_LABELS, $storeId, 0);
    }

    public function isShowSeveralOnPlace(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_SHOW_SEVERAL, $storeId);
    }

    public function getLabelsAlignment(?int $storeId = null): string
    {
        return $this->readString(self::PATH_LABELS_ALIGNMENT, $storeId, 'left');
    }

    public function getMarginBetween(?int $storeId = null): int
    {
        return $this->readInt(self::PATH_MARGIN_BETWEEN, $storeId, 10);
    }

    public function isHideIfZeroLabel(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_HIDE_IF_ZERO, $storeId);
    }

    // ── Selectors ──────────────────────────────────────────────────────────
    public function getCategoryImageSelector(?int $storeId = null): string
    {
        return $this->readString(self::PATH_SEL_CATEGORY, $storeId, '.product-image-photo');
    }

    public function getProductPageImageSelector(?int $storeId = null): string
    {
        return $this->readString(self::PATH_SEL_PRODUCT_PAGE, $storeId, '.gallery-placeholder');
    }

    // ── On Sale ────────────────────────────────────────────────────────────
    public function getSaleMinAmount(?int $storeId = null): float
    {
        return (float) $this->readString(self::PATH_SALE_MIN, $storeId, '0');
    }

    public function getSaleMinPercent(?int $storeId = null): float
    {
        return (float) $this->readString(self::PATH_SALE_MIN_PERCENT, $storeId, '0');
    }

    public function getSaleRounding(?int $storeId = null): string
    {
        return $this->readString(self::PATH_SALE_ROUNDING, $storeId, 'round');
    }

    // ── Is New ─────────────────────────────────────────────────────────────
    public function isUseNewDates(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_NEW_USE_DATES, $storeId);
    }

    public function isUseCreationDate(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_NEW_USE_CREATION, $storeId);
    }

    // ── Stock Status ───────────────────────────────────────────────────────
    public function isOutOfStockOnly(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_STOCK_OOS_ONLY, $storeId);
    }

    public function getDefaultStockLabelId(?int $storeId = null): ?int
    {
        $value = $this->readString(self::PATH_STOCK_DEFAULT_LABEL, $storeId, '');
        return $value !== '' ? (int)$value : null;
    }

    // ── Tooltip ────────────────────────────────────────────────────────────
    public function isTooltipEnabled(?int $storeId = null): bool
    {
        return $this->readFlag(self::PATH_TOOLTIP_ENABLED, $storeId);
    }

    public function getTooltipBackgroundColor(?int $storeId = null): string
    {
        return $this->readString(self::PATH_TOOLTIP_BG, $storeId, '#222222');
    }

    public function getTooltipTextColor(?int $storeId = null): string
    {
        return $this->readString(self::PATH_TOOLTIP_TEXT_COLOR, $storeId, '#ffffff');
    }
}
