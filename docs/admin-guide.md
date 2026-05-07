# Admin Guide

## Accessing Badger

**Catalog → Badger → Manage Badgers**

The grid lists all badgers with their name, enabled status, priority, and schedule dates. From the grid you can inline-edit the name and enabled flag, use mass actions (enable, disable, delete), or open the full edit form.

---

## Creating a Badger

Click **Add Badger**. The form is divided into collapsible fieldsets.

### General

| Field | Description |
|---|---|
| **Name** | Internal label; not shown to customers. Required. |
| **Enabled** | Toggle on/off without deleting. |
| **Priority** | Integer. Higher value wins when multiple badgers match the same product and surface. |
| **Exclusive** | When enabled, this badger suppresses all lower-priority badgers on the same surface for the same product. |
| **Use for Parent** | Bubble this badge up to the configurable parent product when triggered by a child variant. |

### Schedule

Leave both fields empty for a permanently active badger.

| Field | Description |
|---|---|
| **Active From** | The badger becomes active at this date/time. Cron checks every 15 minutes. |
| **Active To** | The badger deactivates after this date/time. Cron checks every 15 minutes. |

### Visibility

| Field | Description |
|---|---|
| **Store Views** | Which store views show this badger. Leave empty or select "All Store Views" to show everywhere. |
| **Customer Groups** | Which customer groups see this badger. Leave empty to show to all groups. |

### Visuals

Each row defines how the badger appears on **one surface**. You can have one row per surface, so a single badger can show with different visuals on the category grid versus the product detail page.

Click **Add Visual** to add a row.

#### Visual Fields

| Field | Description |
|---|---|
| **Surface** | Where this visual appears. See [Surface Reference](#surface-reference). |
| **Shape** | The type of label. See [Shape Reference](#shape-reference). |
| **Placement** | Position on the product image. See [Placement Reference](#placement-reference). |
| **Size %** | Width of the label as a percentage of the product image width. Default: 25%. |
| **Label Text** | Text to display. Supports [placeholder tokens](placeholders.md). Visible when Shape ≠ Image. |
| **Background Color** | Label background colour (hex). Visible when Shape ≠ Image. |
| **Text Color** | Label text colour (hex). Visible when Shape ≠ Image. |
| **Image** | Uploaded or predefined image file. Visible when Shape = Image. |
| **Alt Text** | Alt attribute for the image. Visible when Shape = Image. |
| **Redirect URL** | Makes the badge a clickable link. Optional. |

#### Surface Reference

| Value | Display Location |
|---|---|
| Category Grid | Category listing pages |
| Product Page | Product detail page |
| Cart Cross-Sell | Cross-sell block on the cart page |
| Related | Related products block |
| Upsell | Upsell products block |

#### Shape Reference

| Shape | Description |
|---|---|
| Text | Coloured rectangle with label text. Colours set by Background Color / Text Color. |
| Image | User-uploaded or predefined image file. |
| Rounded Rect | GD-generated rounded rectangle. Colours set by Background Color / Text Color. |
| Circle | GD-generated circle. Colours set by Background Color / Text Color. |
| Ribbon | GD-generated corner ribbon. |

#### Placement Reference

```
┌──────────────────────────┐
│ Top-Left   Top-Ctr  Top-Right │
│                              │
│ Mid-Left   Mid-Ctr  Mid-Right │
│                              │
│ Bot-Left   Bot-Ctr  Bot-Right │
└──────────────────────────┘
```

### Tooltip

An optional hover tooltip that appears when a customer hovers over the badge.

| Field | Description |
|---|---|
| **Enabled** | Toggle tooltip on/off. |
| **Body** | Tooltip text. Supports [placeholder tokens](placeholders.md). |
| **Background Color** | Tooltip background (hex). Falls back to global default from System Config. |
| **Text Color** | Tooltip text colour (hex). Falls back to global default. |

### Product Conditions

Define which products the badger applies to. Leave blank to match all products.

Click **Add Condition** to build a rule tree. Available condition types:

- **Conditions Combination** — AND/OR group of sub-conditions
- **Product is on sale** — special price is active and below regular price
- **Product is new** — within the "new" date range or created recently (configurable)
- **Stock status** — in stock or out of stock
- **Quantity in stock** — numeric range
- **Product Attribute** — any catalog attribute (name, SKU, price, category, custom attributes, etc.)

---

## Duplicating a Badger

On the grid, use the **Actions** column dropdown → **Duplicate**. A copy is created with the same settings; its name is prefixed with "Copy of".

---

## Mass Actions

Select one or more rows on the grid and choose from the **Actions** dropdown:

- **Enable** — set `is_enabled = 1`
- **Disable** — set `is_enabled = 0`
- **Delete** — permanently remove

---

## Image Upload

In the Visual section, when Shape is set to **Image**, an upload widget appears. You can:

- **Drag and drop** an image file onto the drop zone
- **Click** the drop zone to open a file picker
- Choose from **predefined images** (the built-in SVG gallery)

Supported formats: PNG, JPG/JPEG, GIF, WebP, SVG.  
Uploaded files are stored at `pub/media/iranimij/badger/user/`.

---

## Save Buttons

| Button | Action |
|---|---|
| **Save** | Save and return to the grid |
| **Save and Continue Edit** | Save and stay on the edit form |
| **Back** | Return to the grid without saving |
| **Delete** | Delete this badger (confirmation required) |
