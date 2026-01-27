# Category UI Components Documentation

## Overview

This document covers the UI components and patterns used in the category management system, focusing on the recently updated translation integration and accessibility improvements.

## Component Architecture

### Category Show Page
**File**: `resources/views/livewire/pages/category/show.blade.php`

The category show page implements a responsive design with:
- Mobile-first filter panels
- Accessibility-compliant close buttons
- Contextual help text
- Progressive enhancement with Alpine.js

### Key UI Patterns

#### Filter Panel with Close Button
```php
<button type="button"
        class="rounded-full border border-sage/30 p-2 text-sage transition hover:border-sage hover:bg-sage/10"
        @click="showFilters = false"
        aria-label="{{ __('categories.index_close') }}">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
    </svg>
</button>
```

**Features**:
- Semantic HTML with proper ARIA labeling
- Consistent styling with design system
- Hover states and transitions
- Screen reader accessibility

#### Filter Guidance Text
```php
<p class="text-sm leading-relaxed text-sage/80">
    {{ __('categories.show_adjust_filters') }}
</p>
```

**Purpose**:
- Guides users on filter functionality
- Improves user experience
- Provides contextual help

## Design System Integration

### Color Palette
- **Primary**: `sage` - Main brand color
- **Background**: `dark` - Dark theme support
- **Text**: `white`, `sage/80` - Contrast-compliant text colors
- **Borders**: `sage/30` - Subtle borders and dividers

### Typography Scale
- **Headings**: `text-xl font-semibold` for section titles
- **Body**: `text-sm leading-relaxed` for descriptions
- **Labels**: `text-xs font-semibold uppercase tracking-wide` for form labels

### Spacing System
- **Padding**: `p-6` for main content areas
- **Gaps**: `gap-2`, `gap-3` for component spacing
- **Margins**: Minimal use, prefer gap on containers

## Responsive Behavior

### Mobile-First Approach
```php
<div x-cloak x-show="showFilters" class="fixed inset-0 z-40 lg:hidden">
    <!-- Mobile filter overlay -->
</div>

<aside class="hidden lg:col-span-3 lg:block">
    <!-- Desktop sidebar -->
</aside>
```

### Breakpoint Strategy
- **Mobile**: Full-screen overlays for filters
- **Tablet**: Slide-out panels
- **Desktop**: Fixed sidebar layout

## Accessibility Features

### ARIA Labels
All interactive elements include proper ARIA labeling:
```php
aria-label="{{ __('categories.index_close') }}"
```

### Keyboard Navigation
- Focus management for modal dialogs
- Tab order preservation
- Escape key handling for overlays

### Screen Reader Support
- Semantic HTML structure
- Descriptive text for actions
- Status announcements for dynamic content

## State Management

### Alpine.js Integration
```javascript
x-data="{ showFilters: false, viewMode: 'grid' }"
```

**State Variables**:
- `showFilters`: Controls filter panel visibility
- `viewMode`: Toggles between grid and list views

### Livewire Integration
```php
wire:click="$toggle('sidebarOpen')"
wire:model.live="sortBy"
```

**Reactive Properties**:
- Filter state synchronization
- Real-time search updates
- Sort order management

## Performance Considerations

### Lazy Loading
```php
wire:loading.delay.longer
```

### Image Optimization
- Responsive images with proper sizing
- Lazy loading for off-screen content
- WebP format support where available

### Caching Strategy
- Translation caching in production
- Component-level caching for static content
- Database query optimization

## Testing Strategy

### Component Testing
```php
test('category filter panel opens and closes correctly', function () {
    Livewire::test(CategoryShow::class)
        ->assertSee(__('categories.show_adjust_filters'))
        ->call('toggleFilters')
        ->assertSet('showFilters', true);
});
```

### Accessibility Testing
- Screen reader compatibility
- Keyboard navigation flow
- Color contrast validation
- Focus management verification

## Browser Compatibility

### Supported Browsers
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Progressive Enhancement
- Core functionality without JavaScript
- Enhanced experience with Alpine.js
- Graceful degradation for older browsers

## Related Components

### Filter Components
- `@livewire('category.filters')` - Main filter logic
- `x-category.tree` - Category hierarchy display
- `@livewire('components.product-filter-widget')` - Advanced filters

### Shared Components
- `x-shared.button` - Consistent button styling
- `x-container` - Layout container
- `x-meta` - SEO metadata

## Future Enhancements

### Planned Improvements
- Voice search integration
- Advanced filter presets
- Personalized recommendations
- Enhanced mobile gestures

### Performance Optimizations
- Virtual scrolling for large lists
- Intersection Observer for lazy loading
- Service Worker for offline support

## Changelog

### 2024-01-07
- Added accessibility improvements to close buttons
- Implemented contextual help text for filters
- Enhanced ARIA labeling throughout component
- Updated translation integration for better UX