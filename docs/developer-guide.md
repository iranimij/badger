# Developer Guide

## Adding a Custom Placeholder Token

Implement `PlaceholderResolverInterface` and register it in `di.xml`. See [Placeholders → Adding a Custom Token](placeholders.md#adding-a-custom-token) for the full walkthrough.

---

## Adding a Custom Condition

Implement `SpecificationInterface` and a matching `SpecificationFactoryInterface`. Register the factory in the `SpecificationRegistry`'s DI array. See [Conditions → Adding a Custom Specification](conditions.md#adding-a-custom-specification) for the full walkthrough.

---

## Injecting into a New Surface

1. Create a new `Surface` enum case (requires a module override or a fork — enum values cannot be extended via DI in PHP 8.1).

2. Add a layout handle for the surface. Example for a wishlist page:

```xml
<!-- view/frontend/layout/wishlist_index_index.xml -->
<page>
    <body>
        <referenceContainer name="your.container">
            <block class="Iranimij\Badger\Block\Surface\BadgerStack"
                   name="iranimij.badger.wishlist"
                   template="Iranimij_Badger::surface/stack.phtml">
                <arguments>
                    <argument name="surface" xsi:type="string">CATEGORY_GRID</argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

The `surface` argument is the enum case name as a string. `BadgerStack::getSurface()` resolves it via `surfaceFromCode()`.

---

## Implementing BestsellerLookupInterface

By default the `BestsellerSpec` condition always returns false because the default `NullBestsellerLookup` is wired in `di.xml`. Supply your own lookup to activate it:

```php
namespace MyVendor\MyModule\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface;
use Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory;

class BestsellerLookup implements BestsellerLookupInterface
{
    private array $cache = [];

    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {}

    public function isBestseller(int $productId, int $storeId): bool
    {
        $key = $productId . '_' . $storeId;
        if (!array_key_exists($key, $this->cache)) {
            // your best-seller query logic
            $this->cache[$key] = false; // replace with real check
        }
        return $this->cache[$key];
    }
}
```

Register it:

```xml
<preference for="Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface"
            type="MyVendor\MyModule\Model\Evaluation\BestsellerLookup"/>
```

---

## Customising the Admin Form

The admin form uses Magento UI components. All four fieldset sections are built by form modifier classes:

| Modifier | Fieldset |
|---|---|
| `Ui\DataProvider\Form\Modifier\GeneralModifier` | General, Schedule, Visibility |
| `Ui\DataProvider\Form\Modifier\VisualModifier` | Visuals dynamic rows |
| `Ui\DataProvider\Form\Modifier\ConditionsModifier` | Product Conditions |
| `Ui\DataProvider\Form\Modifier\TooltipModifier` | Tooltip |

To add a custom field, create a new modifier implementing `Magento\Ui\DataProvider\Modifier\ModifierInterface` and register it in `etc/adminhtml/di.xml`:

```xml
<type name="Magento\Ui\DataProvider\Modifier\Pool">
    <arguments>
        <argument name="modifiers" xsi:type="array">
            <item name="my_modifier" xsi:type="array">
                <item name="class" xsi:type="string">MyVendor\MyModule\Ui\DataProvider\Form\Modifier\MyModifier</item>
                <item name="sortOrder" xsi:type="number">90</item>
            </item>
        </argument>
    </arguments>
</type>
```

---

## Observing Badger Events

The module dispatches no custom events currently. To react to badger saves, use an `around` plugin on `BadgerRepositoryInterface::save`:

```php
namespace MyVendor\MyModule\Plugin;

use Iranimij\Badger\Api\BadgerRepositoryInterface;
use Iranimij\Badger\Api\Data\BadgerInterface;

class AfterBadgerSave
{
    public function afterSave(BadgerRepositoryInterface $subject, BadgerInterface $result): BadgerInterface
    {
        // your logic here
        return $result;
    }
}
```

---

## Running Unit Tests

Tests live in `Test/Unit/`. Run them with PHPUnit from the module root or via Magento's test runner:

```bash
# From the module directory
vendor/bin/phpunit -c phpunit.xml

# Via Magento test runner (from Magento root)
bin/magento dev:tests:run unit --testsuite Iranimij
```

### Running Integration Tests

Integration tests require the Magento integration test bootstrap:

```bash
vendor/bin/phpunit -c src/iranimij/badger/phpunit-integration.xml
```

Ensure `dev/tests/integration/etc/install-config-mysql.php` points to the test database.

---

## Database Schema Changes

The schema is managed via `etc/db_schema.xml` (declarative schema). After making changes:

```bash
bin/magento setup:db-declaration:generate-whitelist --module-name Iranimij_Badger
bin/magento setup:upgrade
```

Do **not** use install/upgrade scripts for schema changes in Magento 2.3+.

---

## Media Storage

Uploaded images are stored under `pub/media/iranimij/badger/user/`. GD-generated shape images are cached under `pub/media/iranimij/badger/generated/` using a hash of the shape spec (size, colour, type) as the filename, so identical shapes are only generated once.

Access the media storage service directly if you need to programmatically manage images:

```php
use Iranimij\Badger\Service\Media\BadgerMediaStorage;

// Upload from a $_FILES key
$result = $storage->upload('image'); // ['file' => 'filename.png', 'path' => 'iranimij/badger/user/filename.png']

// Delete
$storage->delete('iranimij/badger/user/filename.png');
```

---

## Key Extension Points Summary

| Extension Point | Interface / Base Class | Registration |
|---|---|---|
| Custom placeholder token | `PlaceholderResolverInterface` | `PlaceholderRegistry` DI array in `di.xml` |
| Custom condition spec | `SpecificationInterface` + `SpecificationFactoryInterface` | `SpecificationRegistry` DI array in `di.xml` |
| Bestseller lookup | `BestsellerLookupInterface` | `<preference>` in `di.xml` |
| Admin form field | `Magento\Ui\DataProvider\Modifier\ModifierInterface` | `Magento\Ui\DataProvider\Modifier\Pool` DI array |
| Post-save hook | Plugin on `BadgerRepositoryInterface::save` | `<type>` plugin in `di.xml` |
| Custom config provider | `Iranimij\Core\Model\Config\ConfigProviderAbstract` | Extend and inject |
