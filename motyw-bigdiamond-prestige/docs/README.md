# BigDIAMOND White Prestige Theme

Premium luxury jewelry e-commerce theme for WordPress + WooCommerce, built for BigDIAMOND brand in Krak?w, Poland.

## ?? Overview

**bigdiamond-white-prestige** is a high-performance, SEO-optimized child theme of GeneratePress designed specifically for luxury jewelry e-commerce. It features the White Prestige design system with sophisticated typography, elegant color palette, and premium user experience.

### Key Features

- **White Prestige Design System** - Minimalist elegance with Inter + Playfair Display typography
- **WooCommerce Optimized** - Full product customization, checkout flow, and email templates
- **Custom Design Projects** - Bespoke jewelry commission management system
- **Ring Configurator Integration** - External configurator with webhook integration
- **SEO & Performance First** - Core Web Vitals optimized, E-E-A-T compliant, Schema.org markup
- **Accessibility** - WCAG 2.1 AA compliant
- **Modular Architecture** - Clean, maintainable codebase with clear separation of concerns

## ?? Technical Specifications

- **WordPress**: 6.7+
- **PHP**: 8.3+
- **WooCommerce**: Latest LTS
- **Parent Theme**: GeneratePress
- **Build Tools**: Vite + Tailwind CSS 3.4
- **Standards**: WordPress Coding Standards, PHPCS

## ?? Quick Start

```bash
# Clone repository
git clone <repository-url> wp-content/themes/bigdiamond-white-prestige

# Install dependencies
cd bigdiamond-white-prestige
npm install

# Build assets
npm run build

# Development mode
npm run dev
```

## ?? Project Structure

```
bigdiamond-white-prestige/
??? inc/                      # PHP modules
?   ??? core/                 # Core theme functionality
?   ??? woo/                  # WooCommerce integration
?   ??? seo/                  # SEO & Schema.org
?   ??? performance/          # Performance optimization
?   ??? custom-design/        # Custom design projects CPT
?   ??? ring-configurator/    # Ring configurator integration
?   ??? acf/                  # ACF configuration
?   ??? content/              # Blocks & shortcodes
??? template-parts/           # Template partials
??? woocommerce/              # WooCommerce template overrides
??? assets/                   # Frontend assets
?   ??? css/                  # Stylesheets
?   ??? js/                   # JavaScript
?   ??? images/               # Images & icons
??? docs/                     # Documentation
??? acf-json/                 # ACF field groups (JSON)
??? languages/                # Translation files
??? functions.php             # Main entry point
??? style.css                 # Theme stylesheet
```

## ?? Design System

### Typography

- **UI Font**: Inter (300, 400, 500, 600, 700)
- **Display Font**: Playfair Display (400, 500, 600, 700)

### Colors

- **Background**: `#FAFAFA` (Cream white)
- **Accent**: `#D4AF37` (Gold)
- **Text**: `#2F2F2F` (Charcoal)
- **Gray Scale**: Tailwind-based gray palette

### Spacing

Based on 8px grid system with Tailwind utilities.

## ??? WooCommerce Features

- Custom product loop with enhanced card design
- Product Detail Page (PDP) with 4C diamond information
- Optimized checkout flow with RODO compliance
- Custom transactional emails with brand styling
- Product filters (material, stone, color, theme)
- Gift wrapping option
- Installment information display

## ?? Custom Design Projects

Complete workflow for bespoke jewelry commissions:

- Custom Post Type with status workflow
- Customer brief intake form
- Design file management (concepts, CAD, photos)
- Status notifications via email
- REST API for frontend submission
- Customer dashboard integration

## ?? Ring Configurator

Integration with external ring configurator:

- Webhook endpoint for configuration data
- Secure HMAC signature validation
- Product mapping to WooCommerce cart
- Configuration summary page
- Email notifications

## ?? Performance

- Critical CSS inlining
- Lazy loading (images, iframes)
- Asset optimization (async/defer)
- Fragment caching
- DNS prefetching
- Resource hints (preload, prefetch)

**Target Metrics**:
- CSS: ? 60 KB
- JS: ? 150 KB
- LCP: < 2.5s
- FID/INP: < 100ms
- CLS: < 0.1

## ?? SEO Features

- Automatic product image alt tags
- Open Graph & Twitter Card meta
- JSON-LD Schema (Product, Offer, FAQ, LocalBusiness)
- XML Sitemap customization
- Breadcrumb schema
- Canonical URLs for variations

## ?? Documentation

- [File Structure](FILE_STRUCTURE.md) - Complete file tree with descriptions
- [Import Guide](IMPORT_GUIDE.md) - Setup and development environment
- [WooCommerce Guide](WOOCOMMERCE_GUIDE.md) - Shop customization
- [Custom Design Guide](CUSTOM_DESIGN_GUIDE.md) - Bespoke jewelry workflow
- [Ring Configurator API](RING_CONFIGURATOR_API.md) - Webhook integration
- [SEO & Schema](SEO_SCHEMA.md) - SEO implementation

## ?? Security

- Nonce verification on all forms
- HMAC webhook signature validation
- Input sanitization & output escaping
- Rate limiting on API endpoints
- IP whitelisting (optional)

## ?? Internationalization

- Text Domain: `bigdiamond-white-prestige`
- Translation Ready: All strings wrapped in `__()`
- POT file generation: `wp i18n make-pot . languages/bigdiamond-white-prestige.pot`

## ?? Contributing

This is a proprietary theme for BigDIAMOND brand. Internal development only.

### Code Standards

- Follow WordPress Coding Standards
- Use PHPCS for linting: `composer run lint:php`
- ESLint for JavaScript: `npm run lint:js`
- Use proper prefixes: `bdwp_` for functions, `BDWP_` for constants

### Git Workflow

- `main` - Production-ready code
- `staging` - Pre-production testing
- `dev` - Active development
- Feature branches: `feature/name`, `fix/name`

## ?? Support

For issues or questions:
- **Developer Contact**: [Your contact]
- **Documentation**: `/docs` directory
- **Issue Tracker**: [Internal tracker URL]

## ?? License

Proprietary - ? BigDIAMOND Krak?w. All rights reserved.

---

**Version**: 1.0.0
**Last Updated**: 2025-10-31
