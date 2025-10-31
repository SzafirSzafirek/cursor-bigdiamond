# WooCommerce Customization Guide

Complete guide to WooCommerce integration and customization in BigDIAMOND White Prestige theme.

## Product Taxonomies

### Custom Attributes

#### Material (`pa_material`)
- Bia?e z?oto (White gold)
- ???te z?oto (Yellow gold)
- R??owe z?oto (Rose gold)
- Platyna (Platinum)
- Srebro (Silver)

#### Stone (`pa_kamien`)
- Diament (Diamond)
- Szafir (Sapphire)
- Rubin (Ruby)
- Szmaragd (Emerald)
- Inne (Other)

#### Color (`pa_kolor`)
- Srebrny (Silver)
- Z?oty (Gold)
- R??owy (Rose)
- Bia?y (White)

#### Theme/Motif (`pa_motyw`)
- Klasyczny (Classic)
- Nowoczesny (Modern)
- Vintage
- Minimalistyczny (Minimalist)

**Note**: `pa_gramatura` and `pa_rozmiar` excluded from SEO/descriptions.

## Product Detail Page (PDP)

### Custom Sections

1. **Product Gallery**: Enhanced with zoom and slider
2. **Pricing**: With sale badge and installment info
3. **Specifications Table**: Material, stone, color (excludes weight/size)
4. **Availability**: Stock status and shipping time
5. **4C Panel** (diamonds only): Cut, Color, Clarity, Carat
6. **Certificates**: Links to authenticity documents
7. **"Why BigDIAMOND"**: Trust indicators
8. **FAQ**: Product-specific with JSON-LD
9. **Related Products**: Smart recommendations
10. **Ring Configurator CTA** (for rings)

### ACF Fields for Products

- `bdwp_has_4c`: Boolean - Enable 4C panel
- `bdwp_4c_cut`: Text - Diamond cut grade
- `bdwp_4c_color`: Text - Diamond color grade
- `bdwp_4c_clarity`: Text - Diamond clarity grade
- `bdwp_4c_carat`: Number - Diamond weight
- `bdwp_certificates`: Repeater - Certificate list
- `bdwp_product_faq`: Repeater - Product FAQs
- `bdwp_shipping_time`: Text - Delivery estimate

## Product Listing Page (PLP)

### Features

- **Layout**: 3-column grid (responsive)
- **Per Page**: 12 products
- **Quick View**: Modal preview
- **Badges**: Sale, Featured, New
- **Filters**: Sticky sidebar with attributes
- **Sorting**: Default, Price, Popularity, Rating, Date

### Custom Product Card

```
???????????????????????
?  [Image + Badges]   ?
?  Quick View Button  ?
???????????????????????
?  Product Title      ?
?  Attributes (2 max) ?
?  Price (bold gold)  ?
?  [Add to Cart] ?    ?
???????????????????????
```

## Checkout Customization

### Modified Fields

**Removed**:
- Company name
- Address line 2

**Required**:
- First name
- Last name
- Phone (moved up)
- Email
- Address
- Postcode
- City

### Additional Features

1. **Estimated Delivery**: Shows expected date
2. **RODO Compliance**: 
   - Terms & conditions checkbox (required)
   - Marketing consent (optional)
3. **Gift Wrapping**: +20 PLN option
4. **Trust Badges**: Security indicators
5. **Payment Icons**: Displayed payment methods

### Checkout Flow

```
Cart ? Checkout (Single Page) ? Payment ? Thank You
       ?
       Estimated Delivery
       Trust Badges
       RODO Checkboxes
```

## Transactional Emails

### Default WooCommerce Emails (Customized)

1. `customer_new_account` - Welcome email
2. `customer_processing_order` - Order confirmation
3. `customer_completed_order` - Order completed + care instructions
4. `customer_refunded_order` - Refund confirmation

### Custom Emails

5. `custom_design_intake` - Custom project received
6. `custom_design_update` - Project status changed
7. `ring_configuration_summary` - Ring config completed

### Email Design

**Template**: `/woocommerce/emails/[email-name].php`
**Styles**: `/assets/css/emails.css`

**Design Elements**:
- Header: Logo + gold border-top (#D4AF37)
- Body: White background, Inter font
- Buttons: Gold background, rounded
- Footer: Gray background, business info

**Placeholders**:
- `{order_number}` - Order #
- `{customer_first_name}` - Customer name
- `{custom_status_label}` - Custom status
- `{tracking_number}` - Shipment tracking

### Testing Emails

```bash
# Test email sending
wp eval "WC()->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger( ORDER_ID );"

# Preview in browser (with plugin)
# Email Log or WP Mail Logging plugin recommended
```

## My Account Customization

### Custom Endpoints

- `custom-projects` - Customer's design projects
- `warranties` - Product certificates
- `wishlist` - Saved items (with YITH)

### Enhanced Order Details

- Timeline visualization
- Tracking number with link
- Return request button (within 30 days)
- Order notes section

## Mini Cart

Location: Header (icon with counter)

Features:
- AJAX add to cart
- Slide-in panel
- Product thumbnails
- Subtotal
- Quick links to cart/checkout

## Product Filters

### Filter Types

1. **Price Range**: Slider
2. **Material**: Checkboxes
3. **Stone**: Checkboxes
4. **Color**: Swatches
5. **Availability**: In stock toggle

### AJAX Filtering

Filters update products without page reload.

Implementation: `/inc/woo/catalog.php` - `bdwp_ajax_filter_products()`

## Cart Enhancements

- **Cross-sells**: Related products
- **Coupon field**: Collapsible
- **Shipping calculator**: Real-time rates
- **Trust badges**: Below cart

## Schema.org Markup

### Product Schema

```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Product Name",
  "offers": {
    "@type": "Offer",
    "price": "1500.00",
    "priceCurrency": "PLN",
    "availability": "InStock"
  },
  "brand": {"@type": "Brand", "name": "BigDIAMOND"},
  "aggregateRating": {...}
}
```

Auto-generated for all products. See `/inc/woo/schema.php`.

## Performance Optimizations

### WooCommerce-Specific

- Disabled cart fragments on non-shop pages
- Lazy load product images (except first)
- Reduced AJAX cart refresh rate
- Cached product queries (1 hour)
- Dequeued unused WooCommerce CSS

### Asset Loading

```php
// Only load WooCommerce JS where needed
if ( is_shop() || is_product() || is_cart() || is_checkout() ) {
    wp_enqueue_script( 'bdwp-woo' );
}
```

## Custom Product Types

### Custom Rings (Configurator)

Special handling for configured rings:

- Virtual product with custom metadata
- Price from configuration
- Custom cart display
- Order meta saved for production

Implementation: `/inc/ring-configurator/mapping.php`

## Troubleshooting

### Common Issues

**Products not displaying**
- Check WooCommerce > Settings > Products > Display
- Verify taxonomies registered correctly
- Clear transient cache

**Images not loading**
- Regenerate thumbnails: `wp media regenerate --yes`
- Check image sizes in `inc/core/setup.php`

**Checkout errors**
- Verify RODO validation in `inc/woo/checkout.php`
- Check WooCommerce system status

**Email not sending**
- Test with WP Mail SMTP plugin
- Verify triggers in `inc/woo/emails.php`

## WooCommerce Settings

### Recommended Configuration

**Products > General**:
- Shop page: [Your shop page]
- Selling location: Poland
- Currency: PLN
- Currency position: After price with space

**Products > Display**:
- Shop page display: Show products
- Category display: Show products
- Default category: Wszystkie

**Products > Inventory**:
- Hold stock: 60 minutes
- Notifications: Low stock = 5, Out of stock = 0

**Tax > Standard rates**:
- Country: PL
- Rate: 23%
- Shipping: Taxable

## Extending WooCommerce

### Add Custom Product Tab

```php
add_filter( 'woocommerce_product_tabs', 'bdwp_custom_tab' );
function bdwp_custom_tab( $tabs ) {
    $tabs['custom_tab'] = array(
        'title'    => __( 'Tab Title', 'bigdiamond-white-prestige' ),
        'priority' => 50,
        'callback' => 'bdwp_custom_tab_content'
    );
    return $tabs;
}

function bdwp_custom_tab_content() {
    echo '<h2>Custom Content</h2>';
}
```

### Custom Product Badge

```php
add_action( 'woocommerce_before_shop_loop_item_title', 'bdwp_custom_badge' );
function bdwp_custom_badge() {
    global $product;
    if ( get_post_meta( $product->get_id(), '_custom_badge', true ) ) {
        echo '<span class="custom-badge">New</span>';
    }
}
```

---

For webhook integration and ring configurator, see `RING_CONFIGURATOR_API.md`.
