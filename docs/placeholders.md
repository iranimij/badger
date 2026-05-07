# Placeholder Tokens

Placeholder tokens let you embed live product data in any label text or tooltip body. They are resolved at render time against the current product and badger.

---

## Syntax

Wrap a token name in double curly braces:

```
{{token_name}}
```

Tokens are case-sensitive and must match exactly. Unknown tokens are passed through unchanged, so a typo like `{{pricce}}` will appear literally in the label.

---

## Available Tokens

### Price & Discount

| Token | Output | Example |
|---|---|---|
| `{{price}}` | Product regular price, formatted in the current store currency | `$49.99` |
| `{{special_price}}` | Product special price, formatted. Empty string if no special price is active. | `$29.99` |
| `{{discount_amount}}` | Absolute discount (regular price − final price), formatted. | `$20.00` |
| `{{discount_percent}}` | Discount percentage rounded according to [Configuration → On Sale](configuration.md#on-sale-iranimij_badgeron_sale). | `40%` |

**Example:**
```
SAVE {{discount_percent}} — only {{special_price}}!
```
renders as: `SAVE 40% — only $29.99!`

---

### Product Identity

| Token | Output | Example |
|---|---|---|
| `{{sku}}` | Product SKU | `MH01-XS-Black` |
| `{{qty}}` | Available stock quantity (integer) | `12` |

---

### Schedule

| Token | Output | Example |
|---|---|---|
| `{{active_from}}` | Badger's `active_from` datetime. Empty if not set. | `2025-01-15 00:00:00` |
| `{{active_to}}` | Badger's `active_to` datetime. Empty if not set. | `2025-01-31 23:59:59` |
| `{{new_for_days}}` | Remaining days the product is considered "new", based on the global threshold. | `7` |

---

### Product Attributes

| Token | Output |
|---|---|
| `{{attr:attribute_code}}` | The product attribute value for the given attribute code. Works with any text/select/multiselect attribute. |

**Examples:**
```
{{attr:color}}           → Red
{{attr:material}}        → Cotton
{{attr:manufacturer}}    → Luma
```

---

## Hide Zero Values

When a placeholder resolves to `0` (e.g. `{{discount_percent}}` on a non-discounted product), the label may look odd. Enable **Hide Label When Zero** in [Configuration → Display](configuration.md#display-iranimij_badgerdisplay) to automatically hide the entire label in that case.

---

## Using Tokens in Tooltips

Tokens work identically in the **Tooltip Body** field:

```
Regular price: {{price}}
You save: {{discount_amount}} ({{discount_percent}})
```

---

## Adding a Custom Token

1. Implement `PlaceholderResolverInterface`:

```php
namespace MyVendor\MyModule\Service\Placeholder\Resolver;

use Iranimij\Badger\Service\Placeholder\PlaceholderContext;
use Iranimij\Badger\Service\Placeholder\PlaceholderResolverInterface;

class RatingResolver implements PlaceholderResolverInterface
{
    public function token(): string
    {
        return 'rating';
    }

    public function resolve(PlaceholderContext $context): ?string
    {
        // Return null to leave the token unchanged; return '' to remove it.
        $rating = $context->product->getData('average_rating');
        return $rating !== null ? number_format((float) $rating, 1) . '/5' : null;
    }
}
```

2. Register it in `etc/di.xml`:

```xml
<type name="Iranimij\Badger\Service\Placeholder\PlaceholderRegistry">
    <arguments>
        <argument name="resolvers" xsi:type="array">
            <item name="rating" xsi:type="object">MyVendor\MyModule\Service\Placeholder\Resolver\RatingResolver</item>
        </argument>
    </arguments>
</type>
```

The `{{rating}}` token will now resolve to `4.5/5` in any label or tooltip.
