# Invoice Templates - Design Guide & CSS Support

This document provides comprehensive information about invoice template design concepts, styling approaches, and CSS support when working with Puppeteer for PDF generation in SaasBase.

## Overview

Our invoice templates use Puppeteer for PDF generation, which provides full modern CSS support including Flexbox, Grid, and advanced styling features. This allows for sophisticated designs and layouts that weren't possible with mPDF.

## Template Collection

### 1. **Default Template** (`default.hbs`)
**Design Concept**: Clean, professional, and versatile
- **Style**: Modern business standard with subtle shadows and clean typography
- **Target Audience**: General business use, professional services
- **Color Scheme**: Neutral grays with blue accents
- **Typography**: Segoe UI font stack for excellent readability
- **Layout**: Flexbox-based with proper semantic structure

**Key Features**:
- Responsive grid layout for business information
- Card-based design with subtle borders
- Professional color palette (#1f2937, #3b82f6, #f9fafb)
- Clean table design for invoice items
- Proper spacing and visual hierarchy

**CSS Highlights**:
```css
/* Clean, modern styling */
.invoice-container {
    max-width: 800px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

/* Professional header */
.header-section {
    display: flex;
    justify-content: space-between;
    border-bottom: 2px solid #e5e7eb;
}
```

### 2. **Modern Template** (`modern.hbs`)
**Design Concept**: Contemporary design with accent colors and bold styling
- **Style**: Gradient backgrounds, accent colors, bold typography
- **Target Audience**: Creative agencies, modern businesses, tech companies
- **Color Scheme**: Dynamic accent colors with gradient backgrounds
- **Typography**: Bold headings with modern font weights
- **Layout**: Accent-driven design with colored sections

**Key Features**:
- Gradient header backgrounds
- Accent color theming throughout
- Bold typography with varied font weights
- Colored borders and highlights
- Modern card designs with accent borders

**CSS Highlights**:
```css
/* Accent-driven styling */
.accent-bg {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.accent-text {
    color: #3b82f6;
}

.accent-border {
    border-color: #3b82f6;
}
```

### 3. **Minimal Template** (`minimal.hbs`)
**Design Concept**: Clean, minimal design with maximum readability
- **Style**: Minimal styling, focus on content, clean lines
- **Target Audience**: Consultants, freelancers, minimal aesthetic preference
- **Color Scheme**: Monochromatic with minimal color usage
- **Typography**: Simple, readable fonts
- **Layout**: Spacious, clean lines, minimal decoration

**Key Features**:
- Minimal color usage
- Clean typography without heavy styling
- Spacious layouts with ample whitespace
- Simple borders and dividers
- Focus on content over decoration

### 4. **Corporate Template** (`corporate.hbs`)
**Design Concept**: Professional corporate identity
- **Style**: Traditional business layout, conservative styling
- **Target Audience**: Large corporations, formal business environments
- **Color Scheme**: Professional blues and grays
- **Typography**: Traditional business fonts
- **Layout**: Formal structure with clear sections

**Key Features**:
- Traditional business layout
- Conservative color scheme
- Formal typography
- Clear section divisions
- Professional styling throughout

### 5. **Creative Template** (`creative.hbs`) ⭐ *New*
**Design Concept**: Striking design with gradient backgrounds and modern effects
- **Style**: Bold gradients, modern CSS effects, contemporary styling
- **Target Audience**: Creative agencies, design studios, modern businesses
- **Color Scheme**: Blue to purple gradient (#667eea to #764ba2)
- **Typography**: Modern font stack with varied weights
- **Layout**: Card-based with advanced CSS effects

**Key Features**:
- Stunning gradient backgrounds
- Advanced CSS effects (backdrop-filter, box-shadow)
- Modern card-based layout
- Dynamic color schemes
- Professional yet creative aesthetic

**CSS Highlights**:
```css
/* Gradient header with effects */
.header-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

/* Advanced card styling */
.party-card {
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Modern table design */
.items-table {
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border-radius: 8px;
    overflow: hidden;
}
```

### 6. **Clean Template** (`clean.hbs`) ⭐ *New*
**Design Concept**: Minimalist, clean design with excellent readability
- **Style**: Ultra-clean, focused on readability and professionalism
- **Target Audience**: Professional services, consultants, minimalist preference
- **Color Scheme**: Light grays and blues with high contrast
- **Typography**: Highly readable font stack
- **Layout**: Grid-based with clean sections

**Key Features**:
- Ultra-clean design aesthetic
- Excellent readability and contrast
- Organized grid layouts
- Subtle backgrounds and borders
- Professional color palette

**CSS Highlights**:
```css
/* Clean, minimal styling */
.invoice-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

/* Minimal header design */
.header-section {
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

/* Clean card design */
.party-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
```

## Design Principles

### 1. **Typography Hierarchy**
All templates follow consistent typography patterns:
- **H1** (Invoice Title): 2.5rem, bold, primary color
- **H2** (Section Headers): 1.1rem, semibold, dark gray
- **H3** (Subsections): 1rem, medium, secondary color
- **Body Text**: 0.9rem, regular, readable color
- **Small Text**: 0.8rem, for details and metadata

### 2. **Color Systems**
Each template uses a consistent color system:
- **Primary**: Main brand/accent color
- **Secondary**: Supporting colors
- **Neutral**: Grays for backgrounds and borders
- **Text**: High contrast colors for readability

### 3. **Spacing & Layout**
- **Consistent spacing**: 8px, 16px, 24px, 32px, 40px scale
- **Grid systems**: CSS Grid and Flexbox for responsive layouts
- **Card-based designs**: Consistent card styling across templates
- **Proper margins**: Optimized for PDF generation

### 4. **Responsive Design**
All templates are designed to:
- Work well in PDF format
- Scale properly for different content lengths
- Maintain readability at various sizes
- Handle edge cases (long text, missing data)

## CSS Support with Puppeteer

### ✅ **Fully Supported**
- **Modern CSS**: Flexbox, Grid, transforms, animations
- **Advanced selectors**: nth-child, pseudo-elements, combinators
- **CSS Variables**: Custom properties fully supported
- **Modern units**: rem, em, vh, vw, calc()
- **Advanced effects**: Box-shadow, border-radius, gradients
- **Typography**: Advanced font features, text-shadow
- **Responsive**: Media queries, container queries

### 🎨 **Advanced Features Used**
```css
/* CSS Variables for theming */
:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
    --background-color: #f9fafb;
}

/* Modern layouts */
.grid-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.flex-layout {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Advanced effects */
.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    backdrop-filter: blur(10px);
}
```

## Template Structure

### Common Elements
All templates include these standard sections:
1. **Header**: Logo, title, invoice number, dates
2. **Parties**: Seller and buyer information
3. **Items Table**: Line items with calculations
4. **VAT Summary**: Tax breakdown (if applicable)
5. **Totals**: Final amounts summary
6. **Payment Info**: Payment methods and bank details
7. **Footer**: Additional information and branding

### Handlebars Integration
Templates use Handlebars for dynamic content:
```handlebars
<!-- Company information -->
{{#if invoice.seller.name}}
    <div class="company-name">{{invoice.seller.name}}</div>
{{/if}}

<!-- Dynamic styling -->
<div class="total-amount {{#if invoice.isPaid}}paid{{/if}}">
    {{invoice.formattedTotalGross}}
</div>

<!-- Conditional sections -->
{{#if invoice.vatSummary}}
    <div class="vat-summary">
        {{#each invoice.vatSummary}}
            <div class="vat-row">{{vatRateName}}: {{formattedVat}}</div>
        {{/each}}
    </div>
{{/if}}
```

## Best Practices

### 1. **Performance Optimization**
- Use efficient CSS selectors
- Minimize DOM complexity
- Optimize for PDF rendering
- Use appropriate image formats

### 2. **Accessibility**
- High contrast ratios
- Readable font sizes
- Proper semantic HTML
- Clear visual hierarchy

### 3. **Maintainability**
- Consistent naming conventions
- Modular CSS structure
- Clear documentation
- Version control for changes

### 4. **Testing Approach**
- Test with real data
- Verify PDF output quality
- Check different content lengths
- Validate responsive behavior

## Customization Guide

### Adding New Templates
1. Create new `.hbs` file in templates directory
2. Include complete HTML structure with embedded CSS
3. Follow existing naming conventions
4. Add to seeder with proper metadata
5. Test thoroughly with various data

### Modifying Existing Templates
1. Update CSS within `<style>` tags
2. Test changes in PDF output
3. Verify responsive behavior
4. Update documentation

### Color Customization
Templates support dynamic color customization through CSS variables:
```css
:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
    --accent-color: #10b981;
}
```

## Technical Specifications

### PDF Generation
- **Engine**: Puppeteer with headless Chrome
- **Format**: A4 (210mm × 297mm)
- **Margins**: 5mm (top/sides), 10mm (bottom)
- **Font rendering**: Full system font support
- **Image support**: PNG, JPG, SVG, WebP

### Browser Compatibility
Templates are optimized for:
- Chrome/Chromium (Puppeteer)
- Modern CSS features
- Print media queries
- High DPI displays

## Future Enhancements

### Planned Features
- Dynamic theme switching
- Custom font integration
- Interactive elements (for web preview)
- Advanced layout templates
- Template inheritance system

### Template Roadmap
- Industry-specific templates
- Multi-language layouts
- Advanced calculation displays
- Custom branding options

---

**Last Updated**: 2024-01-11  
**Puppeteer Version**: Latest  
**Tested With**: Laravel 11.x, PHP 8.3+  
**CSS Support**: Full modern CSS support