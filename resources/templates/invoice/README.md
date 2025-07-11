# mPDF Invoice Templates - HTML/CSS Support Guide

This document provides comprehensive information about HTML/CSS support and limitations when working with mPDF for invoice templates in SaasBase.

## Overview

Our invoice templates use mPDF library for PDF generation. mPDF has specific limitations compared to modern web browsers, and this guide helps you understand what works and what doesn't.

## CSS Support Status

### ✅ **Supported & Working Well**

#### Basic Typography
- `font-family`, `font-size`, `font-weight`, `font-style`
- `color`, `text-align`, `text-decoration`
- `line-height` (use numeric values, not keywords)

#### Box Model
- `margin`, `padding` (all variations)
- `border`, `border-width`, `border-style`, `border-color`
- `background-color`, `background-image`

#### Tables
- All table properties work excellently
- `border-collapse`, `border-spacing`
- `vertical-align` in table cells
- Complex table layouts are recommended over flexbox/grid

#### Positioning
- `position: relative` (limited)
- `float: left/right` (works but use tables instead)
- `clear: both`

#### Colors
- Hex colors: `#3B82F6`
- RGB colors: `rgb(59, 130, 246)`
- Named colors: `red`, `blue`, etc.
- RGBA with transparency (background only)

### ⚠️ **Partially Supported**

#### Flexbox
- **Status**: Basic support but unreliable
- **Recommendation**: Use tables instead
- **Working**: `display: flex` (basic)
- **Not Working**: `justify-content`, `align-items`, `flex-wrap`

#### Modern CSS Units
- `rem` - Supported since mPDF 5.7, but use `px` when possible
- `em` - Supported but can be inconsistent
- `vh`, `vw` - Not supported

#### Pseudo-elements
- `::before`, `::after` - Limited support
- `::first-line`, `::first-letter` - Not supported

### ❌ **Not Supported**

#### Modern Layout Systems
- **CSS Grid**: `display: grid` - Not supported
- **CSS Flexbox**: Advanced properties not working
- **CSS Transforms**: `transform`, `rotate`, `scale` - Not supported
- **CSS Transitions/Animations**: Not supported

#### Advanced Selectors
- `:nth-child()`, `:nth-of-type()` - Not supported
- `:hover`, `:active`, `:focus` - Not applicable in PDF
- Complex combinators - Limited support

#### Modern CSS Features
- `calc()` - Not supported
- CSS Variables - Not supported
- `@media` queries - Limited support
- `@supports` - Not supported

## Best Practices

### 1. Use Tables for Layout
```html
<!-- ✅ Good: Table-based layout -->
<table class="w-full">
    <tr>
        <td class="w-1/2" style="vertical-align: top;">Left content</td>
        <td class="w-1/2" style="vertical-align: top;">Right content</td>
    </tr>
</table>

<!-- ❌ Avoid: Flexbox layout -->
<div class="flex justify-between">
    <div class="w-1/2">Left content</div>
    <div class="w-1/2">Right content</div>
</div>
```

### 2. Use Inline Styles for Critical Properties
```html
<!-- ✅ Good: Inline styles for positioning -->
<td style="vertical-align: top; padding-right: 16px;">

<!-- ⚠️ Okay: CSS classes for basic styling -->
<p class="text-sm font-bold">
```

### 3. Stick to Basic CSS Properties
```css
/* ✅ Good: Basic properties */
.invoice-header {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-bottom: 12px;
    padding: 8px;
    border-bottom: 2px solid #3B82F6;
}

/* ❌ Avoid: Modern CSS */
.invoice-header {
    display: flex;
    justify-content: space-between;
    transform: translateY(-10px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

### 4. Font Handling
```css
/* ✅ Good: Font stack with fallbacks */
font-family: "DejaVu Sans", "Noto Sans", "Roboto", "Helvetica Neue", Arial, sans-serif;

/* ✅ Good: Standard font sizes */
font-size: 12px; /* Not 1.2rem */
```

## Template Structure

### Header and Footer
- Headers/footers are set via mPDF's `SetHTMLHeader()` and `SetHTMLFooter()`
- Footer includes: "Generated at {datetime} | {appName} | Page {pageNo} of {totalPages}"
- CSS classes work in headers/footers

### Page Breaks
```css
/* Page break control */
page-break-before: always;
page-break-after: always;
page-break-inside: avoid;
```

### Margins and Spacing
- Use `mm` units for page margins: `margin: 20mm;`
- Use `px` for content spacing: `padding: 12px;`

## Common Issues and Solutions

### Issue: Layout Not Aligning Properly
**Solution**: Replace flexbox with table layout

### Issue: Colors Not Showing
**Solution**: Use hex colors instead of CSS variables

### Issue: Fonts Not Loading
**Solution**: Stick to web-safe fonts or configure mPDF font directory

### Issue: Complex Layouts Breaking
**Solution**: Simplify using tables and basic CSS

## Template Files

### Current Templates
- `default.hbs` - Clean, professional layout
- `modern.hbs` - Modern design with accent colors
- `minimal.hbs` - Clean, minimal design
- `corporate.hbs` - Professional corporate design

### CSS File
- `resources/css/invoice-pdf.css` - Main stylesheet with mPDF-compatible classes

## Testing

When testing templates:
1. Always test in PDF output, not browser preview
2. Test with different content lengths
3. Check page breaks and footer positioning
4. Verify all data fields render correctly

## Debugging Tips

1. **HTML Preview**: Use `InvoiceGeneratorService::previewHtml()` to see rendered HTML
2. **Save HTML**: Enable local development HTML saving in `InvoiceGeneratorService`
3. **CSS Issues**: Check browser console for CSS warnings (not all apply to mPDF)
4. **Layout Issues**: Use border debugging: `border: 1px solid red;`

## Resources

- [mPDF Manual](https://mpdf.github.io/) - Official documentation
- [Supported CSS](https://mpdf.github.io/css-stylesheets/supported-css.html) - Complete CSS support matrix
- [mPDF Limitations](https://mpdf.github.io/about-mpdf/limitations.html) - Known limitations

## Migration Notes

When migrating from HTML/CSS to mPDF:
1. Replace all flexbox layouts with tables
2. Convert CSS Grid to table-based layouts
3. Use inline styles for critical positioning
4. Test thoroughly in PDF output
5. Simplify complex CSS animations/transitions

---

**Last Updated**: 2024-07-11
**mPDF Version**: 8.x
**Tested With**: Laravel 10.x, PHP 8.1+