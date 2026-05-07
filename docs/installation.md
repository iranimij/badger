# Installation

## Requirements

- Magento 2.4.6 or higher (tested on 2.4.7-p4 Community Edition)
- PHP 8.1 or higher
- `ext-gd` PHP extension (for GD shape generation)
- Composer 2.x

## Install via Composer

```bash
composer require iranimij/iranimij-core iranimij/iranimij-product-label
```

## Install from Source

If you are working directly from the repository, copy or symlink the module directories into your Magento installation:

```
app/code/Iranimij/Core/          ← contents of src/iranimij/iranimij-core/
app/code/Iranimij/Badger/        ← contents of src/iranimij/iranimij-product-label/
```

Or register the source paths in `composer.json` using path repositories:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "src/iranimij/iranimij-core"
        },
        {
            "type": "path",
            "url": "src/iranimij/iranimij-product-label"
        }
    ],
    "require": {
        "iranimij/iranimij-core": "*",
        "iranimij/iranimij-product-label": "*"
    }
}
```

## Enable & Setup

```bash
# Enable both modules (order matters — Core must come first)
bin/magento module:enable Iranimij_Core Iranimij_Badger

# Run database migrations (creates 6 tables)
bin/magento setup:upgrade

# Compile dependency injection
bin/magento setup:di:compile

# Deploy static assets
bin/magento setup:static-content:deploy -f

# Flush all caches
bin/magento cache:flush
```

## Verify Installation

After the steps above:

1. Open **Catalog → Badger → Manage Badgers** in the Magento Admin. The grid should appear.
2. Open **Stores → Configuration → Iranimij → Badger**. The configuration panel should be visible.
3. Check that the six database tables exist:

```sql
SHOW TABLES LIKE 'iranimij_badger%';
-- iranimij_badger_customer_group
-- iranimij_badger_entity
-- iranimij_badger_index
-- iranimij_badger_store
-- iranimij_badger_tooltip
-- iranimij_badger_visual
```

## Run the Indexer

After installation, run the initial index build so the product-lookup table is populated:

```bash
bin/magento indexer:reindex iranimij_badger_index
```

Check the indexer status:

```bash
bin/magento indexer:status iranimij_badger_index
```

## Upgrade

```bash
composer update iranimij/iranimij-core iranimij/iranimij-product-label
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

## Uninstall

The module ships an `Uninstall` handler that drops all six tables and removes system configuration.

```bash
bin/magento module:uninstall Iranimij_Badger
bin/magento module:uninstall Iranimij_Core   # only if nothing else depends on it
bin/magento setup:upgrade
bin/magento cache:flush
```
