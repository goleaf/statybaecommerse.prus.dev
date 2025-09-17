{{--
    SHARED COMPONENTS INDEX
    
    This file serves as a reference for all available shared components.
    Use this as a quick reference when building new features.
--}}

{{-- 
    BASIC UI COMPONENTS
    ===================
--}}

{{-- Button Component --}}
{{-- 
<x-shared.button variant="primary|secondary|danger|success|warning|ghost" size="sm|md|lg|xl">
    Button Text
</x-shared.button>
--}}

{{-- Card Component --}}
{{--
<x-shared.card padding="p-4|p-6|p-8" shadow="shadow-sm|shadow-md|shadow-lg">
    <x-slot name="header">Header Content</x-slot>
    Main Content
    <x-slot name="footer">Footer Content</x-slot>
</x-shared.card>
--}}

{{-- Input Component --}}
{{--
<x-shared.input 
    type="text|email|password|search|number"
    label="Field Label"
    placeholder="Placeholder text"
    icon="heroicon-o-icon-name"
    :required="true"
    :error="$errors->first('field')"
/>
--}}

{{-- Select Component --}}
{{--
<x-shared.select 
    label="Select Label"
    placeholder="Choose option"
    :options="$optionsArray"
    :required="true"
/>
--}}

{{-- Badge Component --}}
{{--
<x-shared.badge variant="primary|secondary|success|warning|danger|info|gray" size="sm|md|lg">
    Badge Text
</x-shared.badge>
--}}

{{-- 
    LAYOUT COMPONENTS
    =================
--}}

{{-- Section Component --}}
{{--
<x-shared.section 
    title="Section Title"
    description="Section description"
    icon="heroicon-o-icon-name"
    titleSize="text-lg|text-xl|text-2xl|text-3xl"
    :centered="true"
>
    Section Content
</x-shared.section>
--}}

{{-- Page Header Component --}}
{{--
<x-shared.page-header
    title="Page Title"
    description="Page description"
    icon="heroicon-o-icon-name"
    :breadcrumbs="[
        ['title' => 'Home', 'url' => localized_route('home')],
        ['title' => 'Current Page']
    ]"
>
    <x-slot name="actions">
        <x-shared.button>Action Button</x-shared.button>
    </x-slot>
</x-shared.page-header>
--}}

{{-- Modal Component --}}
{{--
<x-shared.modal title="Modal Title" maxWidth="md|lg|xl|2xl" :closeable="true">
    Modal Content
    <x-slot name="footer">
        <x-shared.button>Action</x-shared.button>
    </x-slot>
</x-shared.modal>
--}}

{{-- 
    INTERACTIVE COMPONENTS
    ======================
--}}

{{-- Search Bar Component --}}
{{--
<x-shared.search-bar 
    placeholder="Search placeholder"
    :show-advanced="true"
    :categories="$categories"
    :brands="$brands"
/>
--}}

{{-- Notification Component --}}
{{--
<x-shared.notification 
    type="success|error|warning|info"
    title="Notification Title"
    :dismissible="true"
>
    Notification message
</x-shared.notification>
--}}

{{-- Empty State Component --}}
{{--
<x-shared.empty-state
    title="No items found"
    description="Description text"
    icon="heroicon-o-icon-name"
    action-text="Action Button"
    action-url="{{ route('some.route') }}"
/>
--}}

{{-- Loading Component --}}
{{--
<x-shared.loading 
    type="spinner|skeleton|pulse|dots"
    size="sm|md|lg|xl"
    text="Loading message"
    :overlay="false"
/>
--}}

{{-- 
    E-COMMERCE COMPONENTS
    =====================
--}}

{{-- Product Card Component --}}
{{--
<x-shared.product-card 
    :product="$product"
    :show-quick-add="true"
    :show-wishlist="true"
    :show-compare="true"
    layout="grid|list"
/>
--}}

{{-- Product Grid Component --}}
{{--
<x-shared.product-grid 
    :products="$products"
    title="Grid Title"
    :columns="4"
    :show-pagination="true"
    empty-title="No products"
    empty-action-text="Browse Products"
/>
--}}

{{-- Filter Panel Component --}}
{{--
<x-shared.filter-panel 
    :categories="$categories"
    :brands="$brands"
    :show-search="true"
    :show-price-filter="true"
/>
--}}

{{-- Pagination Component --}}
{{--
<x-shared.pagination 
    :paginator="$products"
    :show-info="true"
/>
--}}

{{-- Breadcrumbs Component --}}
{{--
<x-shared.breadcrumbs 
    :items="[
        ['title' => 'Category', 'url' => localized_route('categories.index')],
        ['title' => 'Current Category']
    ]"
    separator="chevron|slash|dot"
    :show-home="true"
/>
--}}

{{-- 
    USAGE NOTES
    ===========
    
    1. All components support dark mode automatically
    2. Lithuanian translations are default (per project rules)
    3. EUR currency is used for all locales (per project rules)
    4. Components are responsive by default
    5. Accessibility features are built-in
    6. All components support custom CSS classes via attributes
    
    For detailed documentation, see:
    - /docs/shared-components-guide.md
    - /docs/SHARED_COMPONENTS_IMPLEMENTATION.md
--}}
