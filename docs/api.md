# REST API

All endpoints are under `/rest/{store_code}/V1/iranimij-badger` and require a valid admin token with the `Iranimij_Badger::badger_manage` ACL resource.

## Authentication

Obtain an admin token:

```bash
curl -X POST https://your-store.com/rest/V1/integration/admin/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your-password"}'
# returns "abc123tokenstring"
```

Pass the token as a header on every subsequent request:

```
Authorization: Bearer abc123tokenstring
```

---

## Endpoints

### List / Search Badgers

```
GET /V1/iranimij-badger/search
```

Uses Magento's standard `SearchCriteria` query parameters.

**Query Parameters**

| Parameter | Example | Description |
|---|---|---|
| `searchCriteria[filterGroups][0][filters][0][field]` | `is_enabled` | Field name to filter on |
| `searchCriteria[filterGroups][0][filters][0][value]` | `1` | Value to match |
| `searchCriteria[filterGroups][0][filters][0][conditionType]` | `eq` | Condition type (`eq`, `neq`, `like`, `gt`, `lt`, `in`, `null`) |
| `searchCriteria[sortOrders][0][field]` | `priority` | Sort field |
| `searchCriteria[sortOrders][0][direction]` | `DESC` | Sort direction |
| `searchCriteria[pageSize]` | `20` | Items per page |
| `searchCriteria[currentPage]` | `1` | Page number |

**Example — list all enabled badgers sorted by priority:**

```bash
curl "https://your-store.com/rest/V1/iranimij-badger/search?searchCriteria[filterGroups][0][filters][0][field]=is_enabled&searchCriteria[filterGroups][0][filters][0][value]=1&searchCriteria[sortOrders][0][field]=priority&searchCriteria[sortOrders][0][direction]=DESC" \
  -H "Authorization: Bearer abc123tokenstring"
```

**Response:**

```json
{
    "items": [
        {
            "badger_id": 1,
            "name": "Sale Badge",
            "is_enabled": true,
            "priority": 10,
            "is_exclusive": false,
            "use_for_parent": false,
            "active_from": null,
            "active_to": null,
            "conditions_payload": "{...}",
            "store_ids": [0],
            "customer_group_ids": [0, 1, 2, 3],
            "visuals": [...],
            "tooltip": null
        }
    ],
    "search_criteria": {...},
    "total_count": 1
}
```

---

### Get a Single Badger

```
GET /V1/iranimij-badger/{badgerId}
```

```bash
curl https://your-store.com/rest/V1/iranimij-badger/1 \
  -H "Authorization: Bearer abc123tokenstring"
```

---

### Create a Badger

```
POST /V1/iranimij-badger
```

```bash
curl -X POST https://your-store.com/rest/V1/iranimij-badger \
  -H "Authorization: Bearer abc123tokenstring" \
  -H "Content-Type: application/json" \
  -d '{
    "badger": {
        "name": "New Arrivals",
        "is_enabled": true,
        "priority": 5,
        "is_exclusive": false,
        "use_for_parent": false,
        "store_ids": [0],
        "customer_group_ids": [0, 1, 2, 3],
        "visuals": [
            {
                "surface": 0,
                "shape_kind": 0,
                "placement": 0,
                "label_text": "NEW",
                "size_percent": 25,
                "style_payload": "{\"bg_color\":\"#2e7d32\",\"text_color\":\"#ffffff\"}"
            }
        ]
    }
}'
```

**Response:** the created badger object with its assigned `badger_id`.

---

### Update a Badger

```
PUT /V1/iranimij-badger/{badgerId}
```

```bash
curl -X PUT https://your-store.com/rest/V1/iranimij-badger/1 \
  -H "Authorization: Bearer abc123tokenstring" \
  -H "Content-Type: application/json" \
  -d '{
    "badger": {
        "badger_id": 1,
        "name": "Flash Sale",
        "is_enabled": true,
        "priority": 20
    }
}'
```

Only the fields included in the payload are updated. `store_ids`, `customer_group_ids`, `visuals`, and `tooltip` are replaced in full if included; omit them to leave them unchanged.

---

### Delete a Badger

```
DELETE /V1/iranimij-badger/{badgerId}
```

```bash
curl -X DELETE https://your-store.com/rest/V1/iranimij-badger/1 \
  -H "Authorization: Bearer abc123tokenstring"
```

Returns `true` on success.

---

## Badger Object Schema

```json
{
    "badger_id": 1,
    "name": "Sale Badge",
    "is_enabled": true,
    "priority": 10,
    "is_exclusive": false,
    "use_for_parent": false,
    "active_from": "2025-01-01 00:00:00",
    "active_to": "2025-01-31 23:59:59",
    "conditions_payload": "{\"type\":\"Iranimij\\\\Badger\\\\Model\\\\Condition\\\\Combine\",\"aggregator\":\"all\",\"value\":\"1\"}",
    "store_ids": [0],
    "customer_group_ids": [0, 1, 2, 3],
    "visuals": [
        {
            "badger_id": 1,
            "surface": 0,
            "shape_kind": 0,
            "placement": 0,
            "label_text": "SALE",
            "image_path": null,
            "redirect_url": null,
            "alt_text": null,
            "css_class": null,
            "size_percent": 25,
            "style_payload": "{\"bg_color\":\"#e02020\",\"text_color\":\"#ffffff\"}"
        }
    ],
    "tooltip": {
        "badger_id": 1,
        "is_enabled": true,
        "body_text": "Save {{discount_percent}} today!",
        "background_color": "#222222",
        "text_color": "#ffffff"
    }
}
```

### Field Reference

| Field | Type | Description |
|---|---|---|
| `badger_id` | int | Auto-assigned primary key. Omit on create. |
| `name` | string | Internal name (required). |
| `is_enabled` | bool | Active state. |
| `priority` | int | Higher value = higher priority. |
| `is_exclusive` | bool | Suppress lower-priority badges on same surface. |
| `use_for_parent` | bool | Bubble badge to configurable parent product. |
| `active_from` | string\|null | ISO datetime or null. |
| `active_to` | string\|null | ISO datetime or null. |
| `conditions_payload` | string\|null | JSON-encoded conditions tree, or null for "all products". |
| `store_ids` | int[] | Store IDs. `[0]` means all stores. |
| `customer_group_ids` | int[] | Customer group IDs. Empty array means all groups. |
| `visuals` | object[] | Array of visual objects (one per surface). |
| `tooltip` | object\|null | Tooltip object or null if not configured. |

### Visual Object

| Field | Type | Description |
|---|---|---|
| `surface` | int | Surface enum: 0=category grid, 1=product page, 2=cart cross-sell, 3=related, 4=upsell |
| `shape_kind` | int | Shape: 0=text, 1=image, 2=rounded rect, 3=circle, 4=ribbon |
| `placement` | int | Position: 0=top-left … 8=bottom-right (see [Admin Guide](admin-guide.md#placement-reference)) |
| `label_text` | string\|null | Label text with optional placeholder tokens. |
| `image_path` | string\|null | Relative media path (e.g. `iranimij/badger/user/badge.png`). |
| `redirect_url` | string\|null | Click-through URL. |
| `alt_text` | string\|null | Alt attribute for image labels. |
| `css_class` | string\|null | Additional CSS classes. |
| `size_percent` | int | Width as % of product image. Default 25. |
| `style_payload` | string\|null | JSON: `{"bg_color":"#hex","text_color":"#hex"}`. |
