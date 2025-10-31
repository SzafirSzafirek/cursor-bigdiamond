# Ring Configurator API Integration

Complete documentation for integrating external ring configurator with BigDIAMOND WordPress site.

## Architecture Overview

```
Customer ? External Configurator ? Webhook ? WordPress ? WooCommerce Cart
                    ?                   ?
                    |              Configuration
                    |              Saved & Email Sent
                    ??? Return URL ???
```

## Configuration Setup

### WordPress Settings

**Admin > Theme Settings > Konfigurator**

Required fields:
- **External URL**: Full URL to configurator app
- **Webhook Secret**: HMAC key for signature validation
- **Custom Ring Product ID**: WooCommerce product for cart mapping

### Generate Webhook Secret

```bash
# Generate secure random secret
openssl rand -hex 32

# Result example: 4f8d9c2e1a3b5f7e9d2c4a6b8e1f3d5c
```

Save to WordPress options and configurator settings.

## Webhook Endpoint

**URL**: `https://your-site.com/wp-json/bdwp/v1/rings/webhook`

**Method**: POST

**Headers**:
```
Content-Type: application/json
X-BigDiamond-Signature: {hmac_signature}
X-BigDiamond-Timestamp: {unix_timestamp}
```

### Request Payload

```json
{
  "config_id": "uuid-or-unique-id",
  "ring1": {
    "material": "white_gold",
    "finish": "polished",
    "width": 3.5,
    "thickness": 1.8,
    "size": "17",
    "stones": ["diamond"],
    "engraving": "J & K 2025",
    "price": 2500.00,
    "image": "https://configurator.com/renders/ring1.jpg",
    "specs": {
      "purity": "585",
      "stone_count": 5,
      "stone_size": "0.05ct"
    }
  },
  "ring2": {
    "material": "white_gold",
    "finish": "matte",
    "width": 4.0,
    "thickness": 2.0,
    "size": "20",
    "stones": [],
    "engraving": "J & K 2025",
    "price": 2200.00,
    "image": "https://configurator.com/renders/ring2.jpg",
    "specs": {
      "purity": "585",
      "texture": "brushed"
    }
  },
  "customer": {
    "email": "customer@example.com",
    "name": "Jan Kowalski",
    "phone": "+48123456789"
  }
}
```

### Required Fields

**Root level**:
- `config_id`: Unique configuration identifier
- `ring1`: Ring 1 configuration object
- `ring2`: Ring 2 configuration object

**Ring object**:
- `material`: String (material name)
- `price`: Float (price in PLN)

**Optional but recommended**:
- `finish`, `width`, `thickness`, `size`, `stones`, `engraving`, `image`, `specs`

### Response

**Success (200)**:
```json
{
  "success": true,
  "config_id": "uuid",
  "redirect_url": "https://your-site.com/konfigurator-obraczek/podsumowanie?config_id=uuid",
  "message": "Konfiguracja zosta?a zapisana"
}
```

**Error (400/401/500)**:
```json
{
  "code": "error_code",
  "message": "Error description",
  "data": {
    "status": 400
  }
}
```

## Security

### HMAC Signature

**Algorithm**: HMAC-SHA256

**Signature Generation** (configurator side):

```javascript
// Node.js example
const crypto = require('crypto');

const payload = JSON.stringify(requestBody);
const secret = 'your_webhook_secret';
const signature = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');

// Include in header
headers['X-BigDiamond-Signature'] = signature;
headers['X-BigDiamond-Timestamp'] = Math.floor(Date.now() / 1000);
```

**Signature Validation** (WordPress side):

Handled automatically in `/inc/ring-configurator/security.php`

```php
$expected_signature = hash_hmac('sha256', $body, $webhook_secret);
if (!hash_equals($expected_signature, $received_signature)) {
    return new WP_Error('invalid_signature', 'Invalid signature');
}
```

### Timestamp Validation

Requests older than 5 minutes are rejected to prevent replay attacks.

```php
$time_diff = abs(time() - intval($timestamp));
if ($time_diff > 300) {
    return new WP_Error('expired_request', 'Request expired');
}
```

### IP Whitelisting (Optional)

Configure allowed IPs in WordPress options:

```php
update_option('bdwp_ring_webhook_allowed_ips', [
    '192.168.1.100',
    '10.0.0.50'
]);
```

## Configurator Launch

### URL Parameters

When redirecting to external configurator, include:

```
https://configurator.com/rings?
  utm_source=bigdiamond
  &utm_medium=website
  &utm_campaign=ring_configurator
  &return_url=https://your-site.com/konfigurator-obraczek/podsumowanie
  &webhook_url=https://your-site.com/wp-json/bdwp/v1/rings/webhook
  &customer_email=user@example.com
  &customer_name=Jan+Kowalski
```

**Parameters**:
- `return_url`: URL to redirect after configuration
- `webhook_url`: Endpoint to send configuration data
- `customer_email`: Pre-fill customer email (if logged in)
- `customer_name`: Pre-fill customer name

## Data Flow

### 1. Customer Starts Configuration

```
https://bigdiamond.pl/konfigurator-obraczek
     ? (redirects to)
https://configurator.com/rings?params...
```

### 2. Customer Completes Configuration

Configurator sends webhook to WordPress with configuration data.

### 3. WordPress Processes Webhook

1. Validates signature
2. Sanitizes data
3. Stores configuration (transient for 1 hour + permanent post)
4. Sends confirmation email
5. Returns redirect URL

### 4. Customer Redirected to Summary

```
https://bigdiamond.pl/konfigurator-obraczek/podsumowanie?config_id=uuid
```

Displays:
- Ring 1 image + specs + price
- Ring 2 image + specs + price
- Total price
- [Edit Configuration] [Add to Cart] buttons

### 5. Add to Cart

Customer clicks "Add to Cart":

```javascript
// Frontend JavaScript
fetch('/wp-json/bdwp/v1/rings/add-to-cart', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
    },
    body: JSON.stringify({
        config_id: 'uuid'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        window.location.href = data.cart_url;
    }
});
```

WordPress maps configuration to cart items and redirects to cart.

## Add to Cart Endpoint

**URL**: `/wp-json/bdwp/v1/rings/add-to-cart`

**Method**: POST

**Authentication**: WordPress nonce

**Parameters**:
```json
{
  "config_id": "uuid"
}
```

**Process**:
1. Retrieve configuration from transient
2. Map to WooCommerce product(s)
3. Add to cart with custom metadata
4. Return cart URL

**Response**:
```json
{
  "success": true,
  "cart_url": "https://your-site.com/koszyk",
  "items_added": 2,
  "message": "Obr?czki zosta?y dodane do koszyka"
}
```

## Product Mapping

### Option 1: Single Custom Ring Product

Create one WooCommerce product "Custom Rings" and add both rings as separate cart items with metadata.

**Product Settings**:
- Type: Simple
- Virtual: Yes
- Stock management: Disabled
- Price: 0 (overridden by configuration)

**Cart Item Data**:
```php
array(
    'product_id' => 123, // Custom ring product ID
    'quantity' => 1,
    'cart_item_data' => array(
        'custom_ring_data' => $ring1_data,
        'ring_number' => 1,
        'config_id' => 'uuid'
    )
)
```

### Option 2: Dynamic Product Creation

Create temporary products or use WooCommerce Fees API.

## Cart & Order Display

### Cart Display

Shows custom ring details:
- Ring number (1 or 2)
- Material
- Finish
- Width
- Size
- Engraving
- Stones (if any)
- Price (from configuration)

### Order Meta

Configuration data saved to order:
- `_custom_ring_data`: Full ring configuration
- `_ring_number`: 1 or 2
- `_ring_config_id`: Configuration ID reference

### Order Email

Includes ring specifications in order confirmation.

## Email Notifications

### Ring Configuration Summary

**Trigger**: Webhook received with customer email

**Template**: `/woocommerce/emails/ring-configuration-summary.php`

**Subject**: "Twoje skonfigurowane obr?czki - Zam?wienie #{order_number}"

**Content**:
- Both rings with images
- Specifications
- Total price
- [Edit] [Add to Cart] buttons

## Testing

### Test Webhook

```bash
# Generate test signature
SECRET="your_webhook_secret"
PAYLOAD='{"config_id":"test123","ring1":{"material":"white_gold","price":2500},"ring2":{"material":"white_gold","price":2200}}'

SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | sed 's/^.* //')

# Send request
curl -X POST https://your-site.com/wp-json/bdwp/v1/rings/webhook \
  -H "Content-Type: application/json" \
  -H "X-BigDiamond-Signature: $SIGNATURE" \
  -H "X-BigDiamond-Timestamp: $(date +%s)" \
  -d "$PAYLOAD"
```

### Sandbox Mode

Enable debug logging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs: `wp-content/debug.log`

## Error Handling

### Common Errors

**Missing Signature**:
```json
{"code": "missing_signature", "message": "Brak podpisu w ??daniu", "data": {"status": 401}}
```

**Invalid Signature**:
```json
{"code": "invalid_signature", "message": "Nieprawid?owy podpis ??dania", "data": {"status": 401}}
```

**Expired Request**:
```json
{"code": "expired_request", "message": "??danie wygas?o", "data": {"status": 401}}
```

**Missing Fields**:
```json
{"code": "missing_field", "message": "Brakuj?ce pole: ring1", "data": {"status": 400}}
```

**Configuration Not Found** (add-to-cart):
```json
{"code": "config_not_found", "message": "Konfiguracja nie zosta?a znaleziona", "data": {"status": 404}}
```

## Rate Limiting

Default: 60 requests per minute per IP

Exceeded:
```json
{"code": "rate_limit_exceeded", "message": "Too many requests", "data": {"status": 429}}
```

## Monitoring

### Webhook Logs

Location: `wp-content/uploads/webhook-security.log`

Contains:
- Timestamp
- Source IP
- Request headers
- Payload (sanitized)
- Response status

### Security Alerts

Failed authentication attempts trigger email to admin (if enabled):

```php
update_option('bdwp_webhook_security_alerts', true);
```

---

For implementation details, see `/inc/ring-configurator/` modules.
