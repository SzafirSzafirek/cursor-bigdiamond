# File Structure

Complete directory tree and file descriptions for BigDIAMOND White Prestige theme.

## Root Files

```
bigdiamond-white-prestige/
??? style.css              # Theme header and metadata
??? functions.php          # Main entry point, loads all modules
??? screenshot.png         # Theme screenshot (1200x900px recommended)
??? README.md              # Quick reference (points to docs/)
??? .editorconfig          # Editor configuration
??? .gitignore             # Git ignore rules
??? phpcs.xml              # PHP CodeSniffer configuration
??? package.json           # NPM dependencies and scripts
??? vite.config.js         # Vite build configuration
??? tailwind.config.js     # Tailwind CSS configuration
```

## `/inc` - PHP Modules

All backend functionality organized by feature.

### `/inc/core` - Core Theme Features

```
inc/core/
??? setup.php              # Theme setup, image sizes, menus, widgets
??? assets.php             # Enqueue styles/scripts, async loading
??? helpers.php            # Utility functions (icons, formatting, schema data)
```

### `/inc/woo` - WooCommerce Integration

```
inc/woo/
??? setup.php              # WooCommerce support, taxonomies, breadcrumbs
??? catalog.php            # Product archives, filters, sorting, AJAX
??? product.php            # Single product customization (PDP enhancements)
??? pricing.php            # Price display, ranges, sale formatting
??? checkout.php           # Checkout fields, RODO, gift wrapping
??? account.php            # My Account customization, warranties, projects
??? schema.php             # WooCommerce-specific JSON-LD (Product, Offer)
??? emails.php             # Email customization and custom email classes
??? emails/                # Custom email class files
    ??? class-wc-email-custom-design-intake.php
    ??? class-wc-email-custom-design-update.php
    ??? class-wc-email-ring-configuration-summary.php
```

### `/inc/seo` - SEO & Schema.org

```
inc/seo/
??? seo-product.php        # Product-specific SEO (alt tags, OG tags)
??? meta.php               # Meta tags, Twitter Cards, verification
??? schema.php             # General Schema.org (Article, FAQ, LocalBusiness)
??? sitemap.php            # XML Sitemap customization
```

### `/inc/performance` - Performance Optimization

```
inc/performance/
??? critical-css.php       # Inline critical CSS, preload assets
??? lazy-load.php          # Lazy loading images, iframes, backgrounds
??? cache.php              # Browser cache, transients, fragment caching
```

### `/inc/custom-design` - Custom Design Projects

```
inc/custom-design/
??? post-type.php          # Register custom_project CPT, admin columns
??? fields.php             # ACF fields for project details
??? workflow.php           # Status management, transitions, notifications
??? emails.php             # Email triggers for project updates
??? rest.php               # REST API endpoints (submit, status, comments)
```

### `/inc/ring-configurator` - Ring Configurator Integration

```
inc/ring-configurator/
??? routes.php             # Page registration, shortcode, redirects
??? webhooks.php           # Webhook endpoint, data handling
??? mapping.php            # Map configuration to WooCommerce products
??? security.php           # HMAC validation, IP filtering, rate limiting
```

### `/inc/acf` - Advanced Custom Fields

```
inc/acf/
??? options.php            # Register ACF options pages
??? json.php               # ACF JSON save/load points
```

### `/inc/content` - Content Features

```
inc/content/
??? blocks.php             # Register custom Gutenberg blocks
??? shortcodes.php         # Custom shortcodes (contact, products, button)
```

## `/template-parts` - Template Partials

Reusable template components.

```
template-parts/
??? components/            # UI components
?   ??? header-brand.php
?   ??? hero.php
?   ??? offer.php
?   ??? footer.php
??? woocommerce/           # WooCommerce-specific partials
?   ??? product-card.php
?   ??? product-filters.php
?   ??? cart-mini.php
??? layouts/               # Page layout templates
?   ??? sidebar.php
?   ??? newsletter.php
??? blocks/                # ACF block templates
    ??? hero.php
    ??? products.php
    ??? testimonials.php
```

## `/woocommerce` - WooCommerce Template Overrides

Custom templates for WooCommerce pages.

```
woocommerce/
??? archive-product.php              # Shop page
??? taxonomy-product_cat.php         # Category archive
??? taxonomy-product_tag.php         # Tag archive
??? content-product.php              # Product loop item
??? single-product.php               # Product detail page
??? cart/
?   ??? cart.php                     # Cart page
?   ??? mini-cart.php                # Mini cart widget
??? checkout/
?   ??? form-checkout.php            # Checkout form
?   ??? review-order.php             # Order review
?   ??? thankyou.php                 # Thank you page
??? myaccount/
?   ??? dashboard.php                # Account dashboard
?   ??? navigation.php               # Account navigation
?   ??? orders.php                   # Order history
?   ??? form-edit-account.php       # Edit account form
??? single-product/
?   ??? product-image.php            # Product gallery
?   ??? product-thumbnails.php      # Gallery thumbnails
?   ??? add-to-cart/
?   ?   ??? simple.php
?   ?   ??? variable.php
?   ??? tabs/
?       ??? description.php
?       ??? additional-information.php
??? emails/                          # Custom email templates
    ??? customer-new-account.php
    ??? customer-processing-order.php
    ??? customer-completed-order.php
    ??? custom-design-intake.php
    ??? custom-design-update.php
    ??? ring-configuration-summary.php
    ??? plain/                       # Plain text versions
        ??? custom-design-intake.php
        ??? custom-design-update.php
        ??? ring-configuration-summary.php
```

## `/assets` - Frontend Assets

```
assets/
??? css/
?   ??? main.css                     # Main stylesheet (Tailwind + custom)
?   ??? woocommerce.css              # WooCommerce-specific styles
?   ??? custom-design.css            # Custom design module styles
?   ??? emails.css                   # Email template styles
?   ??? editor-style.css             # Block editor styles
??? js/
?   ??? main.js                      # Main JavaScript
?   ??? woo.js                       # WooCommerce interactions
?   ??? configurator.js              # Ring configurator frontend
??? images/
    ??? logo.svg                     # Site logo
    ??? logo.png                     # Logo PNG fallback
    ??? logo-email.png               # Email template logo
    ??? icons/                       # SVG icons
        ??? cart.svg
        ??? heart.svg
        ??? user.svg
        ??? search.svg
```

## `/docs` - Documentation

```
docs/
??? README.md                        # Project overview and quick start
??? FILE_STRUCTURE.md                # This file
??? IMPORT_GUIDE.md                  # Setup and development guide
??? WOOCOMMERCE_GUIDE.md             # WooCommerce customization
??? CUSTOM_DESIGN_GUIDE.md           # Custom design workflow
??? RING_CONFIGURATOR_API.md         # Ring configurator integration
??? SEO_SCHEMA.md                    # SEO and Schema.org implementation
```

## `/acf-json` - ACF Field Groups

```
acf-json/
??? group_custom_project.json        # Custom project fields
??? group_theme_options.json         # Theme settings
??? group_business_info.json         # Business information
??? group_product_extras.json        # Product extra fields (4C, certificates)
```

## `/languages` - Translations

```
languages/
??? bigdiamond-white-prestige.pot    # Translation template
??? pl_PL.po                         # Polish translation (source)
??? pl_PL.mo                         # Polish translation (compiled)
```

## File Naming Conventions

### PHP Files
- **Functions**: `function-name.php` (kebab-case)
- **Classes**: `class-name.php` (kebab-case, with `class-` prefix)
- **Templates**: `template-name.php` (kebab-case)

### CSS/JS Files
- **Main files**: `main.css`, `main.js`
- **Feature files**: `feature-name.css` (kebab-case)
- **Compiled**: `name.min.css`, `name.min.js`

### Function Naming
- **Theme functions**: `bdwp_function_name()` (snake_case)
- **Constants**: `BDWP_CONSTANT_NAME` (SCREAMING_SNAKE_CASE)
- **Classes**: `BDWP_Class_Name` (Pascal_Case with prefix)

### Hook Naming
- **Actions**: `bdwp_action_name`
- **Filters**: `bdwp_filter_name`

## Module Loading Order

As defined in `functions.php`:

1. Core setup (setup, assets, helpers)
2. WooCommerce integration (conditional)
3. SEO & Schema
4. Performance optimization
5. Custom Design Projects
6. Ring Configurator
7. ACF configuration (conditional)
8. Content modules (blocks, shortcodes)

---

**Note**: This structure follows WordPress and WooCommerce best practices with clear separation of concerns, making the codebase maintainable and scalable.
