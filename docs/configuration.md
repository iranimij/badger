# Configuration Reference

**Stores → Configuration → Iranimij → Badger**

All paths are under the `iranimij_badger` section prefix.

---

## General (`iranimij_badger/general`)

| Field | Config Path | Type | Default | Description |
|---|---|---|---|---|
| Enable Badger | `general/enabled` | Yes/No | Yes | Master switch. Disabling this stops all frontend rendering without touching individual badger records. |
| "New" Threshold (days) | `general/new_for_days` | Integer | 14 | How many days after a product's creation date it is considered "new". Used by the `IsNew` condition and the `{{new_for_days}}` placeholder. |

---

## Display (`iranimij_badger/display`)

| Field | Config Path | Type | Default | Description |
|---|---|---|---|---|
| Max Labels per Product | `display/max_labels` | Integer | 0 | Maximum number of labels shown per product per surface. `0` means unlimited. |
| Show Multiple at Same Position | `display/show_several_on_place` | Yes/No | No | Whether two badges with the same placement can stack. |
| Labels Alignment | `display/labels_alignment` | Select | left | Stacking direction when multiple badges share a position. Options: `left`, `center`, `right`. Visible only when **Show Multiple** is enabled. |
| Margin Between Labels | `display/margin_between` | Integer (px) | 10 | Pixel gap between stacked badges. Visible only when **Show Multiple** is enabled. |
| Hide Label When Zero | `display/hide_if_zero_label` | Yes/No | No | Hide a label when a placeholder resolves to `0` (e.g. `{{discount_percent}}` on a non-discounted product). |

---

## CSS Selectors (`iranimij_badger/selectors`)

These selectors tell the frontend JS where to inject the badge stack overlay. Change them if your theme uses non-standard markup.

| Field | Config Path | Default | Description |
|---|---|---|---|
| Category Image Selector | `selectors/category_image` | `.product-image-photo` | CSS selector for the product image element on category pages. |
| Product Page Selector | `selectors/product_page_image` | `.gallery-placeholder` | CSS selector for the gallery container on product detail pages. |

---

## On Sale (`iranimij_badger/on_sale`)

Controls when the **Product is on sale** condition evaluates to `true`.

| Field | Config Path | Type | Default | Description |
|---|---|---|---|---|
| Minimum Discount Amount | `on_sale/sale_min` | Decimal | 0 | A product must have at least this absolute price reduction to be considered "on sale". |
| Minimum Discount Percentage | `on_sale/sale_min_percent` | Decimal | 0 | A product must have at least this percentage discount to be considered "on sale". |
| Discount Percentage Rounding | `on_sale/rounding` | Select | round | How the calculated percentage is rounded before comparison. Options: `round`, `ceil`, `floor`. |

---

## New Products (`iranimij_badger/new`)

Controls when the **Product is new** condition evaluates to `true`.

| Field | Config Path | Type | Default | Description |
|---|---|---|---|---|
| Use "New From / To Date" Attributes | `new/use_new_dates` | Yes/No | Yes | Check the product's `news_from_date` and `news_to_date` catalog attributes. |
| Fall Back to Creation Date | `new/use_creation_date` | Yes/No | Yes | If date attributes are not set, fall back to comparing the product's `created_at` timestamp against the **New Threshold** days. |

---

## Stock Status (`iranimij_badger/stock_status`)

| Field | Config Path | Type | Default | Description |
|---|---|---|---|---|
| Out-of-Stock Mode | `stock_status/out_of_stock_only` | Yes/No | No | When enabled, only show out-of-stock labels on an OOS product; all other labels are suppressed for that product. |
| Default OOS Label | `stock_status/default_label` | Badger dropdown | — | Fallback badger shown when a product goes out of stock and no explicit OOS condition label matches. Visible only when OOS mode is enabled. |

---

## Tooltip (`iranimij_badger/tooltip`)

These are global defaults. Individual badgers can override colours in their Tooltip fieldset.

| Field | Config Path | Type | Default | Description |
|---|---|---|---|---|
| Enable Tooltip | `tooltip/enabled` | Yes/No | Yes | Global toggle for the tooltip feature. Even if a badger has a tooltip configured, it won't show when this is disabled. |
| Default Background Color | `tooltip/background_color` | Text (hex) | `#222222` | Background colour for all tooltips that don't define their own. |
| Default Text Color | `tooltip/text_color` | Text (hex) | `#ffffff` | Text colour for all tooltips that don't define their own. |
