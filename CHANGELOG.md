# Changelog

All notable changes to `Iranimij_Badger` are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). This project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2025-05-07

### Added

#### Core Module (`Iranimij_Core`)
- `Model\Serializer\JsonSerializer` — safe JSON encode/decode wrapper
- `Model\Config\ConfigProviderAbstract` — typed scoped-config getter base
- `Service\Filesystem\MediaDirectoryProvider` — media directory helpers
- `Service\Deploy\PubMediaResolver` — pub/media file deployment

#### Admin
- Full CRUD admin grid and edit form under **Catalog → Badger**
- Inline grid editing (name, enabled flag)
- Mass actions: enable, disable, delete
- Duplicate action (single and mass)
- Form fieldsets: General, Schedule, Visibility, Visuals, Tooltip, Product Conditions
- Dynamic-rows Visual section with shape-dependent field toggling (text vs. image fields)
- Native Magento color picker for Background Color and Text Color
- Drag-and-drop / click image upload widget with predefined-image gallery
- Drag-and-drop rule condition builder with AND/OR grouping

#### Domain Model
- Six database tables: `iranimij_badger_entity`, `iranimij_badger_store`, `iranimij_badger_customer_group`, `iranimij_badger_visual`, `iranimij_badger_tooltip`, `iranimij_badger_index`
- PHP 8.1 backed enums: `Surface`, `Placement`, `ShapeKind`
- `BadgerRepository` with `save`, `getById`, `getList` (SearchCriteria), `deleteById`
- `BadgerHydrator` for array ↔ entity conversion

#### Conditions Engine
- Admin rule builder using Magento's `Rule` framework
- Custom condition types: `OnSale`, `IsNew`, `StockStatus`, `Qty`
- Conditions tree serialised as JSON (`conditions_payload` column)
- Composable Specification tree for runtime evaluation: `CompositeSpecification`, `OnSaleSpec`, `IsNewSpec`, `StockStatusSpec`, `AttributeEqualsSpec`, `AttributeInSpec`, `AttributeRangeSpec`, `PriceRangeSpec`, `QuantityRangeSpec`, `CustomerGroupSpec`, `StoreSpec`, `CategoryMembershipSpec`, `BestsellerSpec`
- `BestsellerLookupInterface` extension point (default: no-op)

#### Placeholder Tokens
- Template renderer pipeline with DI-injected resolver registry
- Built-in tokens: `{{price}}`, `{{special_price}}`, `{{discount_amount}}`, `{{discount_percent}}`, `{{sku}}`, `{{qty}}`, `{{active_from}}`, `{{active_to}}`, `{{new_for_days}}`, `{{attr:*}}`

#### Frontend Rendering
- Five surfaces: category grid, product page, cart cross-sell, related, upsell
- Nine placement positions (top-left through bottom-right)
- `BadgerStack` block with `IdentityInterface` cache tags (`iranimij_badger_p_{id}_s_{store}`)
- Priority sorting and exclusivity policy
- Per-store and per-customer-group visibility filtering
- `use_for_parent` flag to bubble child badger to configurable parent
- AJAX endpoint for variant-swap re-render on configurable products
- Hover tooltip (`badger-tooltip.js`)
- `badger-positioner.js` for category/widget surface injection
- `badger-variant-swap.js` for configurable swatch swap re-render

#### Media & Shape Rendering
- Image upload to `pub/media/iranimij/badger/user/`
- GD-based shape generation (rounded rectangle, circle, ribbon) cached under `pub/media/iranimij/badger/generated/`
- Predefined image gallery (6 built-in SVG badges)

#### Schedule & Cron
- Cron jobs: `iranimij_badger_activate` (*/15), `iranimij_badger_deactivate` (*/15), `iranimij_badger_isnew_refresh` (0 2 * * *)
- `active_from` / `active_to` scheduling

#### Indexer & Mview
- `iranimij_badger_index` flat table indexer
- Mview subscriber scoped to price, stock, and entity change tables
- `bin/magento indexer:reindex iranimij_badger_index`

#### REST API
- `GET /V1/iranimij-badger/search` — SearchCriteria list
- `GET /V1/iranimij-badger/{id}` — get by ID
- `POST /V1/iranimij-badger` — create
- `PUT /V1/iranimij-badger/{id}` — update
- `DELETE /V1/iranimij-badger/{id}` — delete

#### System Configuration
- `iranimij_badger/general` — enable, new_for_days
- `iranimij_badger/display` — max_labels, show_several, alignment, margin, hide_if_zero
- `iranimij_badger/selectors` — category_image, product_page_image
- `iranimij_badger/on_sale` — sale_min, sale_min_percent, rounding
- `iranimij_badger/new` — use_new_dates, use_creation_date
- `iranimij_badger/stock_status` — out_of_stock_only, default_label
- `iranimij_badger/tooltip` — enabled, background_color, text_color

#### Testing
- PHPUnit unit tests for: serializer, config provider, hydrator, repository, all 12 condition specs, composite specification, tree builder/serializer, shape spec/renderer/cache, media storage, form data provider, all form modifiers, save controller, all placeholder resolvers, template renderer, placeholder registry, badger stack, view model, resolver, all frontend plugins, all cron jobs, priority resolver, exclusivity policy, indexer, payload validator

#### Setup
- Declarative schema (`db_schema.xml` + whitelist)
- `Uninstall` handler (drops tables + removes config)
- `DeployExampleBadgers` data patch
