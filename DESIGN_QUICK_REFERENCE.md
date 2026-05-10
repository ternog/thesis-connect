# 🎨 Design System Quick Reference

## Quick Start

### Import Styles
All styles are automatically imported via `index.css`. For page-specific styles:

```javascript
import './ComponentName.css';
```

## Common Patterns

### 1. Page Container
```html
<div className="page-container">
  <div className="page-header">
    <h1 className="page-title">Page Title</h1>
    <div className="page-actions">
      <button className="btn btn-primary">Action</button>
    </div>
  </div>
  <!-- Content -->
</div>
```

### 2. Card Component
```html
<div className="card">
  <div className="card-header">
    <h3 className="card-title">Card Title</h3>
  </div>
  <div className="card-body">
    <!-- Content -->
  </div>
  <div className="card-footer">
    <!-- Actions -->
  </div>
</div>
```

### 3. Form Layout
```html
<form className="form-container">
  <div className="form-header">
    <h2 className="form-title">Form Title</h2>
  </div>
  
  <div className="form-row">
    <div className="form-group">
      <label className="form-label form-label-required">Name</label>
      <input type="text" className="form-input" />
      <span className="form-help">Helper text</span>
    </div>
  </div>
  
  <div className="form-actions">
    <button type="submit" className="form-btn form-btn-primary">Submit</button>
    <button type="button" className="form-btn form-btn-secondary">Cancel</button>
  </div>
</form>
```

### 4. Table Layout
```html
<div className="table-wrapper">
  <div className="table-header">
    <h3 className="table-title">Table Title</h3>
    <div className="table-actions">
      <div className="table-search">
        <input type="search" placeholder="Search..." />
      </div>
    </div>
  </div>
  
  <div className="table-container">
    <table className="table">
      <thead>
        <tr>
          <th className="sortable">Column</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Data</td>
        </tr>
      </tbody>
    </table>
  </div>
  
  <div className="table-footer">
    <div className="table-info">Showing 1-10 of 100</div>
    <div className="table-pagination">
      <!-- Pagination buttons -->
    </div>
  </div>
</div>
```

### 5. Button Variants
```html
<!-- Primary -->
<button className="btn btn-primary">Primary</button>

<!-- Secondary -->
<button className="btn btn-secondary">Secondary</button>

<!-- Outline -->
<button className="btn btn-outline">Outline</button>

<!-- Danger -->
<button className="btn btn-danger">Delete</button>

<!-- Success -->
<button className="btn btn-success">Approve</button>

<!-- Sizes -->
<button className="btn btn-sm">Small</button>
<button className="btn">Medium</button>
<button className="btn btn-lg">Large</button>

<!-- Loading -->
<button className="btn btn-primary" disabled>
  <span className="btn-loading">Loading...</span>
</button>
```

### 6. Status Badges
```html
<span className="badge badge-success">Approved</span>
<span className="badge badge-warning">Pending</span>
<span className="badge badge-error">Rejected</span>
<span className="badge badge-info">Info</span>
```

### 7. Alerts
```html
<div className="alert alert-success">Success message</div>
<div className="alert alert-error">Error message</div>
<div className="alert alert-warning">Warning message</div>
<div className="alert alert-info">Info message</div>
```

### 8. Grid Layouts
```html
<!-- Auto-fit grid -->
<div className="grid grid-cols-3 gap-lg">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>

<!-- Responsive grid -->
<div className="stat-cards-grid">
  <!-- Cards automatically adjust -->
</div>
```

### 9. Stat Cards
```html
<div className="stat-card stat-card-primary">
  <div className="stat-card-content">
    <div className="stat-card-info">
      <div className="stat-card-label">Total Users</div>
      <div className="stat-card-value">1,234</div>
    </div>
    <div className="stat-card-icon">
      👥
    </div>
  </div>
</div>
```

### 10. Empty States
```html
<div className="empty-state">
  <div className="empty-state-icon">📭</div>
  <h3 className="empty-state-title">No Data Found</h3>
  <p className="empty-state-description">
    There are no items to display at this time.
  </p>
  <button className="btn btn-primary">Add New Item</button>
</div>
```

## CSS Variables Reference

### Colors
```css
/* Primary */
var(--primary-500)  /* Main primary color */
var(--primary-600)  /* Darker primary */
var(--primary-700)  /* Darkest primary */

/* Semantic */
var(--success)      /* Green */
var(--warning)      /* Orange */
var(--error)        /* Red */
var(--info)         /* Blue */

/* Grays */
var(--gray-50)      /* Lightest */
var(--gray-100)
var(--gray-200)
...
var(--gray-900)     /* Darkest */
```

### Spacing
```css
var(--spacing-xs)   /* 4px */
var(--spacing-sm)   /* 8px */
var(--spacing-md)   /* 16px */
var(--spacing-lg)   /* 24px */
var(--spacing-xl)   /* 32px */
var(--spacing-2xl)  /* 48px */
var(--spacing-3xl)  /* 64px */
```

### Typography
```css
var(--font-size-xs)   /* 12px */
var(--font-size-sm)   /* 14px */
var(--font-size-base) /* 16px */
var(--font-size-lg)   /* 18px */
var(--font-size-xl)   /* 20px */
var(--font-size-2xl)  /* 24px */
var(--font-size-3xl)  /* 30px */
var(--font-size-4xl)  /* 36px */
```

### Border Radius
```css
var(--radius-sm)    /* 4px */
var(--radius-md)    /* 8px */
var(--radius-lg)    /* 12px */
var(--radius-xl)    /* 16px */
var(--radius-full)  /* 9999px - circular */
```

### Shadows
```css
var(--shadow-xs)
var(--shadow-sm)
var(--shadow-md)
var(--shadow-lg)
var(--shadow-xl)
var(--shadow-2xl)
```

### Transitions
```css
var(--transition-fast)  /* 150ms */
var(--transition-base)  /* 200ms */
var(--transition-slow)  /* 300ms */
```

## Utility Classes

### Spacing
```html
<!-- Margin -->
<div className="mt-lg mb-xl">  <!-- margin-top, margin-bottom -->
<div className="m-md">          <!-- all margins -->

<!-- Padding -->
<div className="p-lg">          <!-- all padding -->
<div className="pt-md pb-lg">  <!-- padding-top, padding-bottom -->
```

### Typography
```html
<h1 className="text-3xl font-bold text-primary">
<p className="text-sm text-gray-600">
<span className="text-center font-semibold">
```

### Layout
```html
<!-- Flexbox -->
<div className="flex items-center justify-between gap-md">
<div className="flex-col gap-lg">

<!-- Grid -->
<div className="grid grid-cols-3 gap-lg">
```

### Display
```html
<div className="hide-mobile">   <!-- Hidden on mobile -->
<div className="show-mobile">   <!-- Visible only on mobile -->
```

## Responsive Breakpoints

```css
/* Mobile First Approach */
@media (max-width: 640px)  { /* Mobile */ }
@media (max-width: 768px)  { /* Tablet */ }
@media (max-width: 1024px) { /* Desktop */ }
```

## Common Animations

### Fade In
```css
animation: fadeIn var(--transition-base);
```

### Slide Up
```css
animation: slideUp var(--transition-base);
```

### Slide Down
```css
animation: slideDown var(--transition-fast);
```

### Spin (Loading)
```css
animation: spin 0.8s linear infinite;
```

## Best Practices

### ✅ Do's
- Use CSS variables for colors and spacing
- Follow mobile-first responsive design
- Add hover states to interactive elements
- Provide loading and empty states
- Use semantic HTML
- Ensure proper contrast ratios
- Add transitions for smooth interactions

### ❌ Don'ts
- Don't use inline styles
- Don't hardcode colors or spacing values
- Don't forget mobile responsiveness
- Don't skip accessibility features
- Don't use !important unless absolutely necessary
- Don't create duplicate styles

## Component Checklist

When creating a new component:
- [ ] Uses CSS variables
- [ ] Has hover states
- [ ] Has focus states
- [ ] Is responsive
- [ ] Has loading state (if applicable)
- [ ] Has empty state (if applicable)
- [ ] Has error state (if applicable)
- [ ] Follows naming conventions
- [ ] Uses proper semantic HTML
- [ ] Accessible (keyboard navigation, ARIA labels)

## File Organization

```
frontend/src/
├── styles/
│   ├── global-improvements.css  (Import first)
│   ├── forms.css
│   └── tables.css
├── pages/
│   └── [PageName]/
│       ├── [PageName].js
│       └── [PageName].css
└── components/
    └── [ComponentName]/
        ├── [ComponentName].js
        └── [ComponentName].css
```

## Getting Help

- Check `DESIGN_SYSTEM_COMPLETE.md` for full documentation
- Review existing components for patterns
- Use browser DevTools to inspect styles
- Test on multiple screen sizes

---

**Quick Tip**: Use browser DevTools to inspect existing components and see which classes and variables are being used!
