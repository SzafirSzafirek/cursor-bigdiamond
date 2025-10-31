# SEO & Schema.org Implementation

Complete guide to SEO optimization and structured data in BigDIAMOND White Prestige theme.

## Auto-Generated SEO Features

### Product Image Alt Tags

Automatically generated from:
1. Product name
2. Material attribute
3. Stone attribute
4. Brand name

**Example**:
```
alt="Pier?cionek zar?czynowy - Bia?e z?oto - Diament - BigDIAMOND Krak?w"
```

**Implementation**: `/inc/seo/seo-product.php` - `bdwp_product_image_alt()`

**Manual override**: Edit image in Media Library

### Image Title & Caption

Auto-generated for product images if empty:
- **Title**: Same as alt text
- **Caption**: Product name + brand

### Product Meta Title

**Format**: `{Product Name} | {Material} | od {Price} | BigDIAMOND Krak?w`

**Example**: `Pier?cionek zar?czynowy | Bia?e z?oto | od 2500 z? | BigDIAMOND Krak?w`

**Note**: Deactivate if using Yoast SEO or Rank Math to avoid conflicts.

### Product Meta Description

Auto-generated from product short description, truncated to 160 characters.

## Open Graph Tags

### Product Pages

Automatically added:
```html
<meta property="og:type" content="product" />
<meta property="og:title" content="Product Name" />
<meta property="og:description" content="Product description" />
<meta property="og:url" content="Product URL" />
<meta property="og:image" content="Product image URL" />
<meta property="product:price:amount" content="2500.00" />
<meta property="product:price:currency" content="PLN" />
<meta property="product:availability" content="in stock" />
<meta property="product:brand" content="BigDIAMOND" />
```

### Twitter Cards

```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@bigdiamond">
<meta name="twitter:title" content="Product Name">
<meta name="twitter:description" content="Description">
<meta name="twitter:image" content="Image URL">
```

## Schema.org Structured Data

All schemas output as JSON-LD in page footer.

### Product Schema

**Location**: Single product pages

**Type**: `Product`

```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Pier?cionek zar?czynowy bia?e z?oto",
  "description": "Elegancki pier?cionek...",
  "sku": "BD-R-001",
  "image": [
    "https://site.com/image1.jpg",
    "https://site.com/image2.jpg"
  ],
  "brand": {
    "@type": "Brand",
    "name": "BigDIAMOND"
  },
  "offers": {
    "@type": "Offer",
    "url": "https://site.com/product",
    "priceCurrency": "PLN",
    "price": "2500.00",
    "availability": "https://schema.org/InStock",
    "priceValidUntil": "2025-12-31",
    "seller": {
      "@type": "Organization",
      "name": "BigDIAMOND"
    }
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "27",
    "bestRating": "5",
    "worstRating": "1"
  },
  "additionalProperty": [
    {
      "@type": "PropertyValue",
      "name": "Materia?",
      "value": "Bia?e z?oto 585"
    }
  ],
  "material": "White Gold"
}
```

**Implementation**: `/inc/woo/schema.php` - `bdwp_product_schema()`

### BreadcrumbList Schema

**Location**: Product pages, categories

```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Strona g??wna",
      "item": "https://site.com"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Sklep",
      "item": "https://site.com/sklep"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "Pier?cionki",
      "item": "https://site.com/kategoria/pierscionki"
    },
    {
      "@type": "ListItem",
      "position": 4,
      "name": "Pier?cionek zar?czynowy",
      "item": "https://site.com/produkt/pierscionek"
    }
  ]
}
```

### Organization / LocalBusiness Schema

**Location**: Homepage, About page

```json
{
  "@context": "https://schema.org",
  "@type": ["Organization", "JewelryStore", "LocalBusiness"],
  "@id": "https://site.com/#organization",
  "name": "BigDIAMOND",
  "url": "https://site.com",
  "logo": {
    "@type": "ImageObject",
    "url": "https://site.com/logo.png"
  },
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+48-123-456-789",
    "contactType": "Customer Service",
    "email": "kontakt@bigdiamond.pl",
    "areaServed": "PL",
    "availableLanguage": "Polish"
  },
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "ul. Floria?ska 1",
    "addressLocality": "Krak?w",
    "postalCode": "31-019",
    "addressCountry": "PL"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "10:00",
      "closes": "18:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Saturday",
      "opens": "10:00",
      "closes": "14:00"
    }
  ],
  "priceRange": "???",
  "paymentAccepted": "Cash, Credit Card, Bank Transfer",
  "sameAs": [
    "https://www.facebook.com/bigdiamond",
    "https://www.instagram.com/bigdiamond"
  ]
}
```

### FAQPage Schema

**Location**: Product pages (if FAQ field populated), FAQ pages

```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Jaka jest gwarancja na pier?cionki?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Wszystkie nasze pier?cionki obj?te s? 2-letni? gwarancj?..."
      }
    }
  ]
}
```

**ACF Field**: `bdwp_product_faq` (repeater with question/answer)

### WebSite Schema with SearchAction

**Location**: Homepage

```json
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "@id": "https://site.com/#website",
  "name": "BigDIAMOND",
  "url": "https://site.com",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://site.com/?s={search_term_string}&post_type=product"
    },
    "query-input": "required name=search_term_string"
  }
}
```

Enables Google search box in SERPs.

### Article Schema

**Location**: Blog posts

```json
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "Post Title",
  "description": "Post excerpt",
  "datePublished": "2025-01-01T10:00:00+00:00",
  "dateModified": "2025-01-02T12:00:00+00:00",
  "author": {
    "@type": "Person",
    "name": "Author Name"
  },
  "publisher": {
    "@type": "Organization",
    "name": "BigDIAMOND",
    "logo": {
      "@type": "ImageObject",
      "url": "https://site.com/logo.png"
    }
  },
  "image": {
    "@type": "ImageObject",
    "url": "https://site.com/featured-image.jpg",
    "width": 1200,
    "height": 630
  }
}
```

## XML Sitemap

### Included Content

- Products (priority: 0.8, changefreq: weekly)
- Product categories
- Product tags
- Blog posts (priority: 0.6, changefreq: monthly)
- Static pages
- Custom Design projects

### Excluded Content

- Cart, Checkout, Account pages (noindex)
- Uncategorized products
- Draft/private content

### Customization

```php
// Exclude specific categories from sitemap
add_filter('wp_sitemaps_posts_query_args', function($args, $post_type) {
    if ('product' !== $post_type) {
        return $args;
    }
    
    $args['tax_query'] = [
        [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => ['hidden'],
            'operator' => 'NOT IN'
        ]
    ];
    
    return $args;
}, 10, 2);
```

## Robots Meta

Auto-generated per page type:

**Shop pages**:
```html
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
```

**Cart/Checkout**:
```html
<meta name="robots" content="noindex, nofollow">
```

**Search results**:
```html
<meta name="robots" content="noindex, follow">
```

## Canonical URLs

Product variations use parent product URL:
```html
<link rel="canonical" href="https://site.com/product/parent-product/">
```

## Geo Meta Tags

**Location**: Homepage

```html
<meta name="geo.region" content="PL-MA">
<meta name="geo.placename" content="Krak?w">
<meta name="geo.position" content="50.0647;19.9450">
<meta name="ICBM" content="50.0647, 19.9450">
```

## Site Verification

Configure in WordPress options:

```php
update_option('bdwp_google_verification', 'google-site-verification-code');
update_option('bdwp_bing_verification', 'bing-verification-code');
update_option('bdwp_pinterest_verification', 'pinterest-verification-code');
```

Outputs:
```html
<meta name="google-site-verification" content="...">
<meta name="msvalidate.01" content="...">
<meta name="p:domain_verify" content="...">
```

## SEO Best Practices

### Product Names

- **Good**: "Pier?cionek zar?czynowy bia?e z?oto diament 0.5ct"
- **Bad**: "Produkt 123"

### Product Descriptions

- Minimum 150 words
- Include keywords naturally
- Mention material, stones, occasion
- Unique per product (no duplicates)

### Alt Tags

- Descriptive, include product name
- Mention key attributes
- No keyword stuffing
- Format: "{Product} - {Material} - {Brand} {Location}"

### URL Structure

```
? /produkt/pierscionek-zareczyny-biale-zloto
? /produkt/p-123
? /produkt/product-name-very-long-url-with-many-words
```

## E-E-A-T Compliance

### Expertise

- Author bios on blog posts
- Team page with jeweler credentials
- Certifications displayed

### Experience

- Customer reviews with photos
- Before/after project galleries
- Video testimonials

### Authoritativeness

- Industry affiliations
- Press mentions
- Awards and recognition

### Trustworthiness

- SSL certificate (HTTPS)
- Clear contact information
- Return policy
- Privacy policy
- Secure payment badges

## Performance for SEO

Core Web Vitals targets:
- **LCP**: < 2.5s
- **FID/INP**: < 100ms
- **CLS**: < 0.1

Implementation: `/inc/performance/` modules

## Testing Tools

- **Google Search Console**: Submit sitemap, check indexing
- **Google Rich Results Test**: https://search.google.com/test/rich-results
- **Schema Markup Validator**: https://validator.schema.org/
- **PageSpeed Insights**: https://pagespeed.web.dev/
- **Lighthouse**: Chrome DevTools

## Monitoring

Weekly checklist:
- [ ] Check Search Console for errors
- [ ] Verify schema markup valid
- [ ] Monitor Core Web Vitals
- [ ] Review indexed pages count
- [ ] Check for crawl errors

---

For module implementation, see `/inc/seo/` and `/inc/woo/schema.php`.
