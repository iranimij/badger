> **Abandoned.** This package is no longer maintained. Use [OpenLabel](https://github.com/iranimij/openlabel) (`iranimij/openlabel`) instead: product labels for Magento 2 and Hyvä, free and MIT.

# Iranimij_Badger — Product Labels for Magento 2

A clean-room, PHP 8.1+ product-label module for Magento 2 (Community & Commerce). Attach text badges, custom images, or GD-generated shapes to any product across every surface of the store — category grid, product page, cart cross-sells, and related/upsell blocks.

## Features

- **Five display surfaces** — category grid, product detail page, cart cross-sell, related products, upsell products
- **Three label types** — freeform text with inline styling, user-uploaded image, GD-generated shape (rounded rectangle, circle, ribbon)
- **Nine placement positions** — top-left through bottom-right
- **Placeholder tokens** — embed live product data (`{{price}}`, `{{discount_percent}}`, `{{sku}}`, and more) in any label text
- **Rule-based conditions** — target products by attribute value, price range, sale status, new status, stock status, category, or any combination
- **Priority & exclusivity** — control which label wins when multiple apply to the same product and surface
- **Store-view & customer-group scoping** — show different labels per store or logged-in segment
- **Scheduling** — `active_from` / `active_to` date ranges with automated cron activation
- **Hover tooltip** — optional tooltip bubble with configurable background/text colour
- **Use-for-parent** — bubble a child product's badge up to the configurable parent
- **REST API** — full CRUD via `/V1/iranimij-badger`
- **Flat indexer + Mview** — fast product-lookup index that stays current with price/status changes
- **Full unit-test suite** — every slice has corresponding PHPUnit tests

## Requirements

| Requirement | Version |
|---|---|
| Magento | 2.4.6+ (tested on 2.4.7-p4) |
| PHP | 8.1+ |
| `ext-gd` | Required for GD shape generation |
| Composer | 2.x |

## Companion Module

`Iranimij_Badger` depends on **`Iranimij_Core`** for the JSON serializer and config-provider base. Both modules ship together; see [Installation](docs/installation.md) for setup steps.

## Quick Start

```bash
# 1. Enable both modules
bin/magento module:enable Iranimij_Core Iranimij_Badger

# 2. Run setup
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush

# 3. Open the admin
# Catalog → Badger → Manage Badgers → Add Badger
```

## Documentation

| Document | Description |
|---|---|
| [Installation](docs/installation.md) | Full setup, requirements, upgrading |
| [Admin Guide](docs/admin-guide.md) | Creating and managing labels |
| [Configuration](docs/configuration.md) | System config reference |
| [Conditions](docs/conditions.md) | Product condition rules |
| [Placeholders](docs/placeholders.md) | Dynamic text tokens |
| [Frontend](docs/frontend.md) | Surfaces, CSS, JS customisation |
| [REST API](docs/api.md) | API endpoints and payloads |
| [Architecture](docs/architecture.md) | Design decisions and internals |
| [Developer Guide](docs/developer-guide.md) | Extending the module |

## License

[OSL-3.0](https://opensource.org/licenses/OSL-3.0)
