# BigDIAMOND White Prestige Theme

Premium luxury jewelry e-commerce theme for WordPress + WooCommerce.

**Version**: 1.0.0  
**Status**: ? Production Ready  
**Last Updated**: 2025-10-31

---

## ?? Quick Start

```bash
# Install dependencies
npm install

# Build production assets
npm run build

# Development mode with watch
npm run dev
```

**Activate**: Appearance > Themes > BigDIAMOND White Prestige

---

## ?? Requirements

- WordPress 6.7+
- PHP 8.3+
- WooCommerce (latest LTS)
- GeneratePress (parent theme)
- Advanced Custom Fields PRO

---

## ?? Project Structure

```
??? inc/                    # PHP modules (80+ files)
?   ??? core/              # Theme setup, assets, helpers
?   ??? woo/               # WooCommerce integration
?   ??? seo/               # SEO & Schema.org
?   ??? performance/       # Performance optimization
?   ??? custom-design/     # Bespoke jewelry CPT
?   ??? ring-configurator/ # External configurator integration
?   ??? acf/               # ACF configuration
?   ??? content/           # Blocks & shortcodes
?
??? template-parts/        # Reusable UI components
??? woocommerce/           # WooCommerce template overrides
??? assets/                # Frontend assets (CSS, JS, images)
??? docs/                  # Complete documentation
??? acf-json/              # ACF field groups
```

---

## ? Key Features

### ??? WooCommerce
- Custom product listing with filters
- Enhanced product detail pages (PDP)
- 4C diamond information panel
- Optimized checkout flow with RODO compliance
- 7 custom transactional emails
- Gift wrapping option
- Customer dashboard with custom endpoints

### ?? Custom Design Projects
- Bespoke jewelry commission system
- Status workflow (brief ? concept ? CAD ? production ? pickup)
- REST API for submissions
- Email notifications
- Customer tracking dashboard

### ?? Ring Configurator
- External configurator webhook integration
- HMAC-SHA256 signature validation
- Automatic cart mapping
- Configuration summary page

### ?? SEO & Performance
- Auto-generated alt tags and meta
- Schema.org markup (Product, LocalBusiness, FAQ)
- Critical CSS inlining
- Lazy loading
- Core Web Vitals optimized
- Target: LCP < 2.5s, CLS < 0.1

### ?? White Prestige Design
- Inter + Playfair Display typography
- Gold accent (#D4AF37)
- Tailwind CSS 3.4
- Mobile-first responsive

---

## ?? Documentation

Complete documentation in `/docs`:

- **[README.md](docs/README.md)** - Overview and quick start
- **[FILE_STRUCTURE.md](docs/FILE_STRUCTURE.md)** - Complete file tree
- **[IMPORT_GUIDE.md](docs/IMPORT_GUIDE.md)** - Setup instructions
- **[WOOCOMMERCE_GUIDE.md](docs/WOOCOMMERCE_GUIDE.md)** - Shop customization
- **[CUSTOM_DESIGN_GUIDE.md](docs/CUSTOM_DESIGN_GUIDE.md)** - Bespoke workflow
- **[RING_CONFIGURATOR_API.md](docs/RING_CONFIGURATOR_API.md)** - Webhook integration
- **[SEO_SCHEMA.md](docs/SEO_SCHEMA.md)** - SEO implementation
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Complete architecture overview

---

## ?? Configuration

### Theme Settings

Navigate to **Admin > Theme Settings**:

1. **Business Information**
   - Company name, phone, email, address
   
2. **Ring Configurator**
   - External URL
   - Webhook secret (HMAC)
   - Custom ring product ID

### Required Pages

Create pages:
- Konfigurator obr?czek (`/konfigurator-obraczek`)
- Projektowanie na zam?wienie (`/projektowanie-na-zamowienie`)
- O nas (`/o-nas`)
- Kontakt (`/kontakt`)

### Menus

Create and assign:
- Primary Navigation (header)
- Footer Navigation
- Legal Links

---

## ??? Development

### NPM Scripts

```bash
npm run dev        # Development with watch
npm run build      # Production build
npm run lint:js    # Lint JavaScript
npm run lint:php   # Lint PHP (PHPCS)
npm run format     # Format code
```

### Coding Standards

- WordPress Coding Standards
- Function prefix: `bdwp_`
- Constant prefix: `BDWP_`
- Text domain: `bigdiamond-white-prestige`

---

## ?? Testing

### Manual Checklist

- [ ] Product listing displays correctly
- [ ] Product detail page shows all sections
- [ ] Add to cart works
- [ ] Checkout completes successfully
- [ ] Emails are sent
- [ ] Custom design form submits
- [ ] Ring configurator webhook works
- [ ] Mobile responsive
- [ ] Cross-browser compatible

### Performance

```bash
# Lighthouse audit
npx lighthouse https://your-site.local --view

# Verify Core Web Vitals
# Use PageSpeed Insights: https://pagespeed.web.dev/
```

### Schema Validation

- https://search.google.com/test/rich-results
- https://validator.schema.org/

---

## ?? Troubleshooting

**Assets not loading**
```bash
npm run build
# Clear cache: WP Rocket or browser
```

**404 on custom pages**
```bash
wp rewrite flush
```

**Webhook signature fails**
- Verify webhook secret matches in both systems
- Check timestamp is within 5 minutes

**ACF fields not showing**
- Ensure ACF PRO is activated
- Sync field groups from JSON

---

## ?? Performance Targets

- **CSS Bundle**: ? 60 KB
- **JS Bundle**: ? 150 KB
- **LCP**: < 2.5s
- **FID/INP**: < 100ms
- **CLS**: < 0.1

---

## ?? Security Features

- Nonce verification on forms
- HMAC webhook signatures
- Input sanitization
- Output escaping
- Rate limiting (60 req/min)
- IP whitelisting (optional)

---

## ?? Deployment

### Production Build

```bash
# Build optimized assets
npm run build

# Files to deploy:
# - inc/
# - template-parts/
# - woocommerce/
# - assets/ (dist files only)
# - acf-json/
# - languages/
# - functions.php
# - style.css
```

### Post-Deployment

1. Clear all caches
2. Flush permalinks: `wp rewrite flush`
3. Test critical flows
4. Monitor error logs
5. Run Lighthouse audit

---

## ?? Translation

- **Text Domain**: `bigdiamond-white-prestige`
- **POT File**: `languages/bigdiamond-white-prestige.pot`
- **Polish**: `languages/pl_PL.po` / `.mo`

Generate POT:
```bash
wp i18n make-pot . languages/bigdiamond-white-prestige.pot
```

---

## ?? Module Overview

### Core (`/inc/core`)
Theme setup, asset management, helper functions

### WooCommerce (`/inc/woo`)
Complete shop integration (8 modules + email classes)

### SEO (`/inc/seo`)
Auto-generated SEO, Schema.org, meta tags, sitemap

### Performance (`/inc/performance`)
Critical CSS, lazy loading, caching

### Custom Design (`/inc/custom-design`)
Bespoke jewelry project management (5 modules)

### Ring Configurator (`/inc/ring-configurator`)
External configurator webhook integration (4 modules)

### ACF (`/inc/acf`)
Options pages, JSON configuration

### Content (`/inc/content`)
Custom blocks and shortcodes

---

## ?? Support

- **Documentation**: `/docs` directory
- **Issue Tracker**: [Internal URL]
- **Developer**: [Contact]

---

## ?? License

Proprietary - ? BigDIAMOND Krak?w. All rights reserved.

---

## ? Completion Status

- ? Core architecture (modular, production-ready)
- ? WooCommerce integration (complete shop customization)
- ? Custom Design module (full workflow)
- ? Ring Configurator (secure webhook integration)
- ? SEO & Schema (comprehensive structured data)
- ? Performance optimization (Core Web Vitals)
- ? Email system (7 branded templates)
- ? Documentation (7 comprehensive guides)

**Total Files**: 80+  
**Code Lines**: ~10,000+  
**Ready for**: Production

---

**Built for BigDIAMOND Krak?w**  
Premium Luxury Jewelry E-commerce
