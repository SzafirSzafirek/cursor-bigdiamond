# Import & Setup Guide

Complete guide to setting up the BigDIAMOND White Prestige theme in your development environment.

## Prerequisites

- **PHP**: 8.3 or higher
- **WordPress**: 6.7 or higher
- **Node.js**: 18.0 or higher
- **Composer**: Latest version (optional, for PHPCS)
- **WooCommerce**: Latest LTS version
- **GeneratePress**: Latest version (parent theme)

### Recommended Tools

- **Local Development**: LocalWP, XAMPP, or Docker
- **Code Editor**: VS Code with recommended extensions
- **Version Control**: Git

## Initial Setup

### 1. Install Parent Theme

```bash
# Download and install GeneratePress
wp theme install generatepress --activate
```

### 2. Install Child Theme

```bash
# Clone repository into themes directory
cd wp-content/themes/
git clone <repository-url> bigdiamond-white-prestige
cd bigdiamond-white-prestige

# Switch to development branch (if applicable)
git checkout dev
```

### 3. Install Dependencies

```bash
# Install NPM packages
npm install

# Install Composer dependencies (optional, for development)
composer install
```

### 4. Build Assets

```bash
# Production build
npm run build

# Development build with watch
npm run dev
```

### 5. Activate Theme

```bash
# Via WP-CLI
wp theme activate bigdiamond-white-prestige

# Or activate via WordPress Admin:
# Appearance > Themes > BigDIAMOND White Prestige > Activate
```

## Required Plugins

### Essential

1. **WooCommerce** (latest)
   ```bash
   wp plugin install woocommerce --activate
   ```

2. **Advanced Custom Fields PRO** (latest)
   - Manual installation required (licensed plugin)
   - Upload via Plugins > Add New > Upload Plugin

### Recommended

3. **WP Rocket** - Performance & caching
4. **Yoast SEO** or **Rank Math** - SEO optimization
5. **YITH WooCommerce Wishlist** - Wishlist functionality

## Configuration Steps

### 1. WooCommerce Setup

```bash
# Run WooCommerce setup wizard
wp wc wizard
```

Configure:
- Store address (Krak?w)
- Currency: PLN
- Payment gateways
- Shipping zones (Poland)
- Tax settings

### 2. Theme Options

Navigate to **Theme Settings** in WordPress Admin:

#### Business Information
- Company name: BigDIAMOND
- Phone: +48 123 456 789
- Email: kontakt@bigdiamond.pl
- Address: [Street], [Postal Code] Krak?w

#### Ring Configurator
- External URL: [Configurator URL]
- Webhook Secret: [Generate secure secret]
- Custom Ring Product ID: [Create and assign]

### 3. Create Required Pages

```bash
# Create pages
wp post create --post_type=page --post_title='Konfigurator obr?czek' --post_name='konfigurator-obraczek' --post_content='[bdwp_ring_configurator]' --post_status=publish

wp post create --post_type=page --post_title='Projektowanie na zam?wienie' --post_name='projektowanie-na-zamowienie' --post_status=publish

wp post create --post_type=page --post_title='O nas' --post_name='o-nas' --post_status=publish

wp post create --post_type=page --post_title='Kontakt' --post_name='kontakt' --post_status=publish
```

### 4. Import Product Attributes

```bash
# Register custom attributes
wp wc product_attribute create --name="Materia?" --slug="pa_material"
wp wc product_attribute create --name="Kamie?" --slug="pa_kamien"
wp wc product_attribute create --name="Kolor" --slug="pa_kolor"
wp wc product_attribute create --name="Motyw" --slug="pa_motyw"
```

### 5. Menu Setup

Create menus:
- **Primary Navigation**: Main header menu
- **Footer Navigation**: Footer links
- **Legal Links**: Privacy, Terms, etc.

Assign in **Appearance > Menus**.

### 6. Permalinks

Set to **Post name** structure:
```
Settings > Permalinks > Post name
```

Flush rewrite rules:
```bash
wp rewrite flush
```

## Development Environment

### VS Code Setup

Recommended extensions:
- PHP Intelephense
- ESLint
- Stylelint
- Tailwind CSS IntelliSense
- WordPress Snippets

#### `.vscode/settings.json`

```json
{
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "php.validate.executablePath": "/usr/bin/php",
  "phpcs.enable": true,
  "phpcs.standard": "WordPress"
}
```

### LocalWP Configuration

1. Create new site
2. Select **Custom** environment
3. Choose PHP 8.3
4. Set site domain (e.g., `bigdiamond.local`)
5. Install WordPress
6. Follow theme setup steps above

### Docker Setup (Alternative)

```yaml
# docker-compose.yml
version: '3.8'

services:
  wordpress:
    image: wordpress:latest
    ports:
      - "8000:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - ./:/var/www/html/wp-content/themes/bigdiamond-white-prestige

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: root
```

Run:
```bash
docker-compose up -d
```

## Build Process

### NPM Scripts

```bash
# Development (watch mode)
npm run dev

# Production build
npm run build

# Build preview
npm run preview

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Lint PHP
npm run lint:php

# Format code
npm run format
```

### Tailwind CSS

Configuration in `tailwind.config.js`. Custom utilities are automatically generated from config.

Purge unused CSS in production:
```bash
npm run build
```

### Vite

- Hot Module Replacement (HMR) enabled in dev mode
- Automatic asset hashing for cache busting
- Code splitting for optimal loading
- CSS extraction and minification

## Database Configuration

### Sample Data Import

```bash
# Import sample products
wp import products.xml --authors=create

# Import ACF field groups
wp acf import --json_file=acf-json/
```

### Custom Tables

Theme doesn't use custom tables, but logs webhooks:
```bash
# Clear webhook logs
rm wp-content/uploads/webhook-security.log
```

## Performance Optimization

### WP Rocket Configuration

1. Cache: Enable page caching
2. File Optimization:
   - Minify CSS files
   - Combine CSS files
   - Remove unused CSS ?
   - Optimize JavaScript delivery
3. Media:
   - Enable LazyLoad
   - Images: WebP
4. Preload:
   - Activate preload
   - Preload fonts

### Server Requirements

Recommended PHP configuration (`php.ini`):

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 3000
```

## Troubleshooting

### Common Issues

**Issue**: Assets not loading
**Solution**: Rebuild assets with `npm run build` and clear cache

**Issue**: ACF fields not showing
**Solution**: Ensure ACF PRO is activated and field groups are synced

**Issue**: 404 on custom pages
**Solution**: Flush permalinks: `wp rewrite flush`

**Issue**: Webhook signature fails
**Solution**: Verify webhook secret matches in configurator settings

**Issue**: Slow admin
**Solution**: Disable unnecessary plugins, enable object caching

### Debug Mode

Enable WordPress debug mode in `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

View logs:
```bash
tail -f wp-content/debug.log
```

## Testing

### Manual Testing Checklist

- [ ] All pages load without errors
- [ ] Product listing displays correctly
- [ ] Product detail pages show all information
- [ ] Add to cart functionality works
- [ ] Checkout process completes
- [ ] Email notifications sent
- [ ] Custom design form submits
- [ ] Ring configurator integration works
- [ ] All menus display correctly
- [ ] Mobile responsive design works
- [ ] Cross-browser compatibility (Chrome, Firefox, Safari)

### Performance Testing

```bash
# Run Lighthouse audit
npx lighthouse https://your-site.local --view

# Test Core Web Vitals
# Use PageSpeed Insights: https://pagespeed.web.dev/
```

## Deployment

### Staging Deployment

1. Sync files to staging server
2. Run `npm run build` on staging
3. Update database URLs
4. Test all functionality

### Production Deployment

```bash
# Build production assets
npm run build

# Sync to production (example with rsync)
rsync -avz --exclude='node_modules' --exclude='.git' ./ user@server:/path/to/theme/

# Or use FTP/SFTP client to upload:
# - inc/
# - template-parts/
# - woocommerce/
# - assets/ (built files only)
# - acf-json/
# - languages/
# - functions.php
# - style.css
```

### Post-Deployment

1. Clear all caches (server, CDN, WP Rocket)
2. Test critical user flows
3. Monitor error logs
4. Run performance audit

## Maintenance

### Regular Tasks

- **Weekly**: Review error logs, update plugins
- **Monthly**: Database optimization, backup verification
- **Quarterly**: Performance audit, security scan

### Backup Strategy

Backup:
- Database (daily)
- Theme files (on changes)
- Uploads directory (daily)
- ACF field groups (on changes)

## Support Resources

- Theme Documentation: `/docs` directory
- WordPress Codex: https://codex.wordpress.org/
- WooCommerce Docs: https://woocommerce.com/documentation/
- Tailwind CSS: https://tailwindcss.com/docs

---

**Last Updated**: 2025-10-31
