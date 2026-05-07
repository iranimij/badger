# Architecture

## Design Goals

- **No Amasty fingerprint.** No class names, table names, routes, events, or cache tags that reference any third-party vendor. A codebase grep for `amasty`, `label_rule`, or `catalog_label` inside the module directories returns zero results.
- **PHP 8.1+ idioms.** Enums replace magic integers. Readonly constructor promotion replaces verbose property declarations. Union types and named arguments where they clarify intent.
- **Testable by design.** The evaluation engine, placeholder pipeline, and shape renderer are all pure PHP classes with no Magento dependencies, so they can be unit-tested without bootstrapping the framework.
- **CQRS-lite split.** The persistence model (`Model\Badger`) handles storage. A separate read model (`ReadModel\ResolvedBadger`) handles presentation. They never leak into each other's concerns.
- **DI-driven extensibility.** Every registry (placeholder resolvers, specification factories, modifiers) is an injected array. Adding a new token or condition type requires only a new class + one `di.xml` entry.

---

## Module Map

```
Iranimij_Core                       Shared utilities (JSON, config, filesystem)
Iranimij_Badger
  ├── Api/                          Service contracts (interfaces)
  ├── Block/Surface/                Layout blocks (BadgerStack, TooltipBlock)
  ├── Controller/
  │   ├── Adminhtml/Badger/         CRUD + mass action controllers
  │   └── Ajax/                     Frontend AJAX endpoint
  ├── Cron/                         Schedule activation / deactivation
  ├── Model/
  │   ├── Badger.php                Persistence entity
  │   ├── BadgerHydrator.php        Array ↔ entity conversion
  │   ├── BadgerRepository.php      Repository implementation
  │   ├── Condition/                Admin rule conditions (Magento Rule framework)
  │   ├── Enum/                     PHP 8.1 backed enums
  │   ├── Evaluation/               Runtime specification tree
  │   │   ├── Specification/        One class per condition type
  │   │   ├── Factory/              Creates specs from serialized tree nodes
  │   │   └── ...
  │   ├── Indexer/                  Flat product-lookup index
  │   ├── ReadModel/                Presentation DTOs + resolver
  │   ├── Resolution/               Priority sorting + exclusivity policy
  │   ├── ResourceModel/            DBAL layer (entity, visuals, tooltip, link tables)
  │   └── Source/                   Admin dropdown option providers
  ├── Plugin/Frontend/              HTML injection into existing blocks
  ├── Service/
  │   ├── Media/                    Image upload storage
  │   ├── Placeholder/              Token resolution pipeline
  │   └── ShapeRenderer/            GD-based shape image generation
  ├── Ui/DataProvider/              Admin UI component data providers & modifiers
  ├── ViewModel/                    Frontend view model (DI-safe block delegation)
  └── view/
      ├── adminhtml/                Admin UI components, templates, JS, CSS
      └── frontend/                 Frontend layout, templates, JS, CSS
```

---

## Data Model

### Entity Relationship

```
iranimij_badger_entity (1)
  ├── (N) iranimij_badger_store          — per-store visibility
  ├── (N) iranimij_badger_customer_group — per-group visibility
  ├── (N) iranimij_badger_visual         — one row per surface
  ├── (1) iranimij_badger_tooltip        — hover tooltip settings
  └── (N) iranimij_badger_index          — denormalised product match index
```

### Why These Design Choices?

| Decision | Rationale |
|---|---|
| Conditions stored as JSON | A condition tree is inherently hierarchical. Normalising it into relational tables would require recursive queries. JSON serialisation matches Magento's Rule framework output format and keeps the store layer simple. |
| Visuals normalised per surface (1:N) | A single badger can appear differently across surfaces (different size, different label) without duplicating the badger record. Each visual row is keyed by `(badger_id, surface)`. |
| Tooltip as a separate 1:1 table | Separates tooltip concerns from visual concerns. Makes it easy to join or omit without touching the visual schema. |
| Link tables for store/group | Standard Magento pattern. Allows efficient `IN (...)` filtering in the repository layer without JSON parsing. |
| Flat index table | The index pre-computes which badgers match which `(product_id, store_id, customer_group_id)` tuples, avoiding expensive condition evaluation on every page render. |

---

## Rendering Pipeline

```
Request → Layout → BadgerStack block
                       │
                       ▼
              BadgerViewModel.getBadgers()
                       │
                       ▼
              BadgerResolver.resolve(product, surface, storeId, groupId)
                       │
              ┌────────┴─────────┐
              │                  │
         Load enabled        Filter by:
          badgers               schedule window
                                store membership
                                customer group
                                conditions (Rule.validate)
                                surface
              │
              ▼
         For each matching visual → hydrate(badger, visual, product)
              ├── render placeholder tokens in label_text / tooltip
              └── build ResolvedBadger DTO
              │
              ▼
         PriorityResolver.sort()       — sort descending by priority
              │
              ▼
         ExclusivityPolicy.apply()     — if any exclusive, return first only
              │
              ▼
         ResolvedBadger[]  →  stack.phtml
```

---

## PHP 8.1 Enums

Three backed enums replace magic integers throughout the codebase:

### `Model\Enum\Surface`
```php
enum Surface: int {
    case CATEGORY_GRID  = 0;
    case PRODUCT_PAGE   = 1;
    case CART_CROSSSELL = 2;
    case RELATED        = 3;
    case UPSELL         = 4;
}
```

### `Model\Enum\Placement`
```php
enum Placement: int {
    case TOP_LEFT      = 0;  case TOP_CENTER    = 1;  case TOP_RIGHT    = 2;
    case MIDDLE_LEFT   = 3;  case MIDDLE_CENTER = 4;  case MIDDLE_RIGHT = 5;
    case BOTTOM_LEFT   = 6;  case BOTTOM_CENTER = 7;  case BOTTOM_RIGHT = 8;
}
```
CSS class: `iranimij-badger--{strtolower(name with _ replaced by -)}` (e.g. `iranimij-badger--top-right`).

### `Model\Enum\ShapeKind`
```php
enum ShapeKind: int {
    case TEXT         = 0;
    case IMAGE        = 1;
    case ROUNDED_RECT = 2;
    case CIRCLE       = 3;
    case RIBBON       = 4;
}
```

---

## Conditions vs. Evaluation Engine

Two layers serve different roles:

| Layer | Class | Used in | Purpose |
|---|---|---|---|
| Conditions | `Model\Condition\Combine` | Admin form, `BadgerResolver` | Magento Rule-compatible; stores as JSON; admin drag-and-drop builder |
| Evaluation | `Model\Evaluation\Specification\*` | Unit tests, future indexer | Composable specification tree; pure PHP; short-circuit evaluation |

The evaluation engine is the target architecture for the indexer and batch evaluation paths. The conditions engine is the user-facing layer. Both use the same `conditions_payload` JSON.

---

## Indexer & Mview

`iranimij_badger_index` is a flat table of `(product_id, store_id, customer_group_id, badger_id)` tuples. It is populated by `BadgerIndexer` and kept current by the Mview subscriber, which watches:

- `iranimij_badger_entity` — any badger save triggers a full-product reindex for that badger
- Price, stock, and status change tables — triggers partial reindex for affected products

The narrow Mview subscription avoids the full-EAV noise of subscribing to `catalog_product_entity_*` tables.

---

## Iranimij_Core

`Iranimij_Core` is a lightweight companion module providing three utilities used by `Iranimij_Badger` (and available to future Iranimij modules):

| Class | Purpose |
|---|---|
| `Model\Serializer\JsonSerializer` | Safe JSON encode/decode wrapper with typed return and graceful fallback |
| `Model\Config\ConfigProviderAbstract` | Base class for typed, scoped config getters (`readString`, `readInt`, `readFlag`, etc.) |
| `Service\Filesystem\MediaDirectoryProvider` | Writable media directory helper with `ensureSubPath()` |
| `Service\Deploy\PubMediaResolver` | Resolves and writes files under `pub/media/` |

All `json_encode` / `json_decode` calls in `Iranimij_Badger` go through `JsonSerializer`. No direct calls to native JSON functions exist in the module.
