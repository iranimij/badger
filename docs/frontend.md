# Frontend Rendering

## How Rendering Works

1. A **`BadgerStack`** block is injected into the layout for each surface.
2. The block calls `BadgerViewModel::getBadgers()`, which calls `BadgerResolver::resolve()`.
3. `BadgerResolver` loads all enabled badgers, filters by schedule / store / customer group / conditions, picks the matching visuals for the requested surface, runs the priority and exclusivity policy, and returns `ResolvedBadger[]`.
4. The `stack.phtml` template renders each resolved badger as an absolutely positioned element inside a `div.iranimij-badger-stack`.
5. The positioner JS attaches the stack overlay to the correct image container element.

---

## Surfaces

| Surface Enum | Value | Layout Handle | Container |
|---|---|---|---|
| `CATEGORY_GRID` | 0 | `catalog_category_view` | Injected via plugin onto each product-item image |
| `PRODUCT_PAGE` | 1 | `catalog_product_view` | `product.info.media` layout container |
| `CART_CROSSSELL` | 2 | `checkout_cart_index` | Injected via plugin |
| `RELATED` | 3 | `catalog_product_view` | Injected via plugin |
| `UPSELL` | 4 | `catalog_product_view` | Injected via plugin |

---

## HTML Structure

A rendered badge stack looks like this:

```html
<div class="iranimij-badger-stack" data-surface="product_page">

  <!-- Text label -->
  <div class="iranimij-badger iranimij-badger--top-left iranimij-badger--shape-text"
       data-badger-id="3"
       style="width:25%;background-color:#e02020;color:#ffffff;">
    <span class="iranimij-badger__label">SALE</span>
  </div>

  <!-- Image label -->
  <a class="iranimij-badger iranimij-badger--top-right iranimij-badger--shape-image"
     data-badger-id="7"
     style="width:20%;"
     href="/special-offers.html">
    <img src="/media/iranimij/badger/user/eco-badge.png" alt="Eco Friendly" loading="lazy"/>
  </a>

</div>
```

The wrapping element is `<a>` when a redirect URL is set, `<div>` otherwise.

---

## CSS Classes

### Stack

| Class | Description |
|---|---|
| `.iranimij-badger-stack` | Absolutely positioned overlay covering the parent container. |

### Badge

| Class | Description |
|---|---|
| `.iranimij-badger` | Base badge element. Absolutely positioned, pointer-events enabled. |
| `.iranimij-badger--top-left` | Placement: top left corner |
| `.iranimij-badger--top-center` | Placement: top centre |
| `.iranimij-badger--top-right` | Placement: top right corner |
| `.iranimij-badger--middle-left` | Placement: vertically centred, left |
| `.iranimij-badger--middle-center` | Placement: dead centre |
| `.iranimij-badger--middle-right` | Placement: vertically centred, right |
| `.iranimij-badger--bottom-left` | Placement: bottom left corner |
| `.iranimij-badger--bottom-center` | Placement: bottom centre |
| `.iranimij-badger--bottom-right` | Placement: bottom right corner |
| `.iranimij-badger--shape-text` | Shape: text label |
| `.iranimij-badger--shape-image` | Shape: image |
| `.iranimij-badger--shape-rounded_rect` | Shape: GD rounded rectangle |
| `.iranimij-badger--shape-circle` | Shape: GD circle |
| `.iranimij-badger--shape-ribbon` | Shape: GD ribbon |
| `.iranimij-badger__label` | Inner `<span>` for text labels. |

The **CSS Class** field in the Visual row appends additional classes to `.iranimij-badger`, allowing per-badge theme overrides.

---

## CSS Customisation

The module ships `Iranimij_Badger::css/badger3.css`. Override any rule in your theme's `web/css/` directory.

### Making the product image container a stacking context

The stack uses `position: absolute`. Its parent container must have `position: relative`. The shipped CSS handles the Luma category grid automatically:

```css
.products-grid .product-item-info { position: relative; overflow: hidden; }
```

For the product page, Luma's `.product.media` already has `position: relative`.

If your theme uses a different container, add:

```css
.your-product-image-wrapper { position: relative; }
```

### Adjusting badge size

`size_percent` is applied as `width: X%` inline on the badge element. Override the `max-width` / `max-height` limits in your theme if needed:

```css
.iranimij-badger { max-width: 60%; max-height: 60%; }
```

---

## JavaScript

### `badger-positioner.js`

Included on category/widget pages. After the page loads it finds each `.iranimij-badger-stack` and positions it as an absolute overlay on top of the matching product image container. The target selector is read from the `window.badgerConfig.categoryImageSelector` value, which comes from the positioner-init template.

### `badger-variant-swap.js`

Included on configurable product pages. When a swatch is selected, it watches for the gallery update event and re-fetches the badge stack for the newly selected variant via the AJAX endpoint (`/badger/ajax/badger`). This ensures that variant-specific conditions (e.g. "out of stock" for a specific size) are re-evaluated.

### `badger-tooltip.js`

Included on all pages via `default.xml`. Binds `mouseenter` / `mouseleave` to any `.iranimij-badger[data-tooltip]` element and shows/hides a floating tooltip bubble.

---

## AJAX Endpoint

`GET /badger/ajax/badger?product_id=X&surface=Y`

Returns HTML for the badge stack for a given product and surface. Used by the variant swap JS. The response is injected directly into the DOM to replace the existing stack.

---

## Cache Tags

`BadgerStack` implements `IdentityInterface` and returns two cache tags per render:

- `iranimij_badger` — invalidated when any badger record changes
- `iranimij_badger_p_{productId}_s_{storeId}` — invalidated when the specific product's badges change

This means a single product's badge cache is invalidated precisely when needed, rather than flushing all badges.

---

## Configurable Products

When `use_for_parent` is enabled on a badger, any condition match on a child simple product bubbles the badge up to the configurable parent. This is useful for "out of stock" or "on sale" badges on configurables where the state lives on a child product.

The variant swap JS (`badger-variant-swap.js`) also re-renders the badge stack when the customer selects a swatch, ensuring the displayed badge reflects the selected variant's actual state.
