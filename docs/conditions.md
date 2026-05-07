# Conditions Engine

The conditions engine lets you target exactly which products a badger applies to. It is built on two separate layers:

1. **Admin condition builder** — the drag-and-drop rule editor in the admin form, built on Magento's `Rule` framework
2. **Evaluation engine** — a composable Specification tree used at render time for fast, testable product matching

---

## Admin Condition Builder

Open a badger's edit form and expand the **Product Conditions** fieldset. Click **Add Condition** to start building a rule.

### Condition Types

#### Conditions Combination
A logical group that can contain any number of child conditions. The group has an operator — **ALL** (AND) or **ANY** (OR) — which determines how children are combined.

You can nest combinations to any depth:
```
ALL of:
  - Price is between 10 and 50
  - ANY of:
      - Product is on sale
      - Product is new
```

#### Product is on sale
True when a product's special price is active and at or below the regular price, subject to the thresholds in [Configuration → On Sale](configuration.md#on-sale-iranimij_badgeron_sale).

#### Product is new
True when a product falls within its configured "new" window. The evaluation depends on the [New Products configuration](configuration.md#new-products-iranimij_badgernew):
- If **Use "New From / To Date"** is on: checks `news_from_date` and `news_to_date` catalog attributes
- If **Fall Back to Creation Date** is on: uses `created_at` within the configured day threshold

#### Stock status
Matches products based on whether they are in stock or out of stock.

#### Quantity in stock
Matches products where the available quantity falls within a numeric range. Useful for low-stock labels.

#### Product Attribute
Match any product catalog attribute (name, SKU, price, weight, category IDs, or any custom attribute) against a value. Supports:
- `is` / `is not`
- `is one of` / `is not one of`
- `contains` / `does not contain`
- `starts with` / `ends with`
- `greater than` / `less than` / `between`

---

## Evaluation Engine (Specification Pattern)

The conditions tree is stored as JSON in the `conditions_payload` column. At render time it is evaluated by `BadgerResolver` using a composable Specification tree.

### Why a Separate Engine?

The Magento Rule framework is excellent for the admin UI but is not designed for rapid batch evaluation. The Specification pattern gives:
- Fully unit-testable classes (no Magento bootstrap needed)
- Clear single-responsibility per condition type
- Short-circuit evaluation (AND stops on first false; OR stops on first true)
- Easy extension via DI without modifying core classes

### EvaluationContext

Every specification receives a readonly `EvaluationContext` DTO:

```php
readonly class EvaluationContext
{
    public function __construct(
        public readonly ProductInterface $product,
        public readonly int $storeId,
        public readonly int $customerGroupId,
        public readonly float $qty = 1.0,
    ) {}
}
```

### Available Specifications

| Class | Token | Description |
|---|---|---|
| `CompositeSpecification` | — | AND or OR group of child specifications |
| `OnSaleSpec` | `on_sale` | Product has an active special price below regular price |
| `IsNewSpec` | `is_new` | Product is within its "new" date window |
| `StockStatusSpec` | `stock_status` | In-stock or out-of-stock check |
| `AttributeEqualsSpec` | `attribute_eq` | Attribute equals a value |
| `AttributeInSpec` | `attribute_in` | Attribute matches one of a set of values |
| `AttributeRangeSpec` | `attribute_range` | Attribute is between min and max |
| `PriceRangeSpec` | `price_range` | Final price is between min and max |
| `QuantityRangeSpec` | `qty_range` | Stock quantity is between min and max |
| `CustomerGroupSpec` | `customer_group` | Customer belongs to a specific group |
| `StoreSpec` | `store` | Request is for a specific store |
| `CategoryMembershipSpec` | `category` | Product belongs to a specific category |
| `BestsellerSpec` | `bestseller` | Product is a bestseller (requires custom `BestsellerLookupInterface` implementation) |

### Adding a Custom Specification

1. Implement `SpecificationInterface`:

```php
namespace MyVendor\MyModule\Model\Evaluation\Specification;

use Iranimij\Badger\Model\Evaluation\EvaluationContext;
use Iranimij\Badger\Model\Evaluation\Specification\SpecificationInterface;

class IsGiftableSpec implements SpecificationInterface
{
    public function isSatisfiedBy(EvaluationContext $context): bool
    {
        return (bool) $context->product->getData('is_giftable');
    }
}
```

2. Create a factory that implements `SpecificationFactoryInterface`:

```php
class IsGiftableFactory implements SpecificationFactoryInterface
{
    public function create(array $params): SpecificationInterface
    {
        return new IsGiftableSpec();
    }
}
```

3. Register it in `etc/di.xml`:

```xml
<type name="Iranimij\Badger\Model\Evaluation\SpecificationRegistry">
    <arguments>
        <argument name="factories" xsi:type="array">
            <item name="is_giftable" xsi:type="object">MyVendor\MyModule\Model\Evaluation\Factory\IsGiftableFactory</item>
        </argument>
    </arguments>
</type>
```

### Implementing BestsellerLookupInterface

By default, the `BestsellerSpec` uses a no-op lookup that always returns false. To activate it, implement and register `BestsellerLookupInterface`:

```php
namespace MyVendor\MyModule\Model\Evaluation;

use Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface;

class MyBestsellerLookup implements BestsellerLookupInterface
{
    public function isBestseller(int $productId, int $storeId): bool
    {
        // your logic: query sales_order_item, a pre-built report, etc.
    }
}
```

Register it in `etc/di.xml`:

```xml
<preference for="Iranimij\Badger\Model\Evaluation\BestsellerLookupInterface"
            type="MyVendor\MyModule\Model\Evaluation\MyBestsellerLookup"/>
```

---

## Condition Persistence

The condition tree is serialized as a JSON string and stored in `iranimij_badger_entity.conditions_payload`. The format follows Magento's standard `Rule::getConditions()->asArray()` schema:

```json
{
    "type": "Iranimij\\Badger\\Model\\Condition\\Combine",
    "aggregator": "all",
    "value": "1",
    "conditions": [
        {
            "type": "Iranimij\\Badger\\Model\\Condition\\OnSale",
            "value": "1"
        }
    ]
}
```

An empty or null `conditions_payload` means "match all products".
