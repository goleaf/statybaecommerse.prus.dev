@section('meta')
    <x-meta
        title="Shared Components Showcase - {{ config('app.name') }}"
        description="Demonstration of all shared components in the application"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Page Header --}}
    <x-shared.page-header
        title="Shared Components Showcase"
        description="Demonstration of all shared components and their variants"
        icon="heroicon-o-squares-2x2"
        :breadcrumbs="[
            ['title' => __('shared.home'), 'url' => route('home')],
            ['title' => 'Component Showcase']
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Buttons Section --}}
        <x-shared.section 
            title="Buttons"
            description="Universal button component with variants and sizes"
            icon="heroicon-o-cursor-arrow-rays"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Button Variants --}}
                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">Button Variants</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.button variant="primary">Primary Button</x-shared.button>
                        <x-shared.button variant="secondary">Secondary Button</x-shared.button>
                        <x-shared.button variant="success">Success Button</x-shared.button>
                        <x-shared.button variant="warning">Warning Button</x-shared.button>
                        <x-shared.button variant="danger">Danger Button</x-shared.button>
                        <x-shared.button variant="ghost">Ghost Button</x-shared.button>
                    </div>
                </x-shared.card>

                {{-- Button Sizes --}}
                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">Button Sizes</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.button variant="primary" size="sm">Small Button</x-shared.button>
                        <x-shared.button variant="primary" size="md">Medium Button</x-shared.button>
                        <x-shared.button variant="primary" size="lg">Large Button</x-shared.button>
                        <x-shared.button variant="primary" size="xl">Extra Large Button</x-shared.button>
                    </div>
                </x-shared.card>
            </div>
        </x-shared.section>

        {{-- Badges Section --}}
        <x-shared.section 
            title="Badges"
            description="Status badges with color variants"
            icon="heroicon-o-tag"
        >
            <x-shared.card>
                <div class="flex flex-wrap gap-4">
                    <x-shared.badge variant="primary">Primary</x-shared.badge>
                    <x-shared.badge variant="secondary">Secondary</x-shared.badge>
                    <x-shared.badge variant="success">Success</x-shared.badge>
                    <x-shared.badge variant="warning">Warning</x-shared.badge>
                    <x-shared.badge variant="danger">Danger</x-shared.badge>
                    <x-shared.badge variant="info">Info</x-shared.badge>
                    <x-shared.badge variant="gray">Gray</x-shared.badge>
                </div>
            </x-shared.card>
        </x-shared.section>

        {{-- Form Components Section --}}
        <x-shared.section 
            title="Form Components"
            description="Input and select components with validation"
            icon="heroicon-o-document-text"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">Input Components</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.input 
                            wire:model="testInput"
                            label="Test Input"
                            placeholder="Enter some text"
                            help-text="This is a help text example"
                        />
                        
                        <x-shared.input 
                            type="email"
                            label="Email Input"
                            placeholder="user@example.com"
                            icon="heroicon-o-envelope"
                        />
                        
                        <x-shared.input 
                            type="search"
                            label="Search Input"
                            placeholder="Search..."
                            icon="heroicon-o-magnifying-glass"
                        />
                    </div>
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">Select Components</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.select 
                            wire:model="testSelect"
                            label="Test Select"
                            placeholder="Choose option"
                        >
                            <option value="option1">Option 1</option>
                            <option value="option2">Option 2</option>
                            <option value="option3">Option 3</option>
                        </x-shared.select>
                    </div>
                </x-shared.card>
            </div>
        </x-shared.section>

        {{-- Notifications Section --}}
        <x-shared.section 
            title="Notifications"
            description="Alert notification system"
            icon="heroicon-o-bell"
        >
            <x-shared.card>
                <div class="flex flex-wrap gap-4">
                    <x-shared.button 
                        wire:click="testNotification('success')"
                        variant="success"
                        size="sm"
                    >
                        Test Success
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="testNotification('error')"
                        variant="danger"
                        size="sm"
                    >
                        Test Error
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="testNotification('warning')"
                        variant="warning"
                        size="sm"
                    >
                        Test Warning
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="testNotification('info')"
                        variant="secondary"
                        size="sm"
                    >
                        Test Info
                    </x-shared.button>
                </div>
            </x-shared.card>
        </x-shared.section>

        {{-- Loading States Section --}}
        <x-shared.section 
            title="Loading States"
            description="Various loading indicators"
            icon="heroicon-o-arrow-path"
        >
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">Spinner</h4>
                    </x-slot>
                    <x-shared.loading type="spinner" size="md" text="Loading..." />
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">Skeleton</h4>
                    </x-slot>
                    <x-shared.loading type="skeleton" size="md" />
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">Pulse</h4>
                    </x-slot>
                    <x-shared.loading type="pulse" size="md" />
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">Dots</h4>
                    </x-slot>
                    <x-shared.loading type="dots" text="Processing..." />
                </x-shared.card>
            </div>
        </x-shared.section>

        {{-- Product Components Section --}}
        @if($this->featuredProducts->count() > 0)
            <x-shared.section 
                title="Product Components"
                description="E-commerce specific components"
                icon="heroicon-o-cube"
            >
                <x-shared.products-grid 
                    :products="$this->featuredProducts"
                    title="Featured Products"
                    :columns="4"
                    :show-pagination="false"
                />
            </x-shared.section>
        @endif

        {{-- Empty State Section --}}
        <x-shared.section 
            title="Empty States"
            description="No-content displays with actions"
            icon="heroicon-o-exclamation-triangle"
        >
            <x-shared.empty-state
                title="No Items Found"
                description="This is an example of an empty state component with action buttons"
                icon="heroicon-o-cube"
                action-text="Browse Products"
                action-url="{{ route('products.index') }}"
            />
        </x-shared.section>

        {{-- Modal Section --}}
        <x-shared.section 
            title="Modal Dialogs"
            description="Modal system with Alpine.js"
            icon="heroicon-o-window"
        >
            <x-shared.card>
                <x-shared.button 
                    wire:click="toggleModal"
                    variant="primary"
                >
                    Open Modal
                </x-shared.button>

                <x-shared.modal 
                    title="Example Modal"
                    :show="$showModal"
                    max-width="md"
                >
                    <p class="text-gray-600 dark:text-gray-300">
                        This is an example modal dialog using the shared modal component.
                    </p>
                    
                    <x-slot name="footer">
                        <x-shared.button 
                            wire:click="toggleModal"
                            variant="secondary"
                        >
                            Close
                        </x-shared.button>
                        
                        <x-shared.button variant="primary">
                            Confirm
                        </x-shared.button>
                    </x-slot>
                </x-shared.modal>
            </x-shared.card>
        </x-shared.section>

        {{-- Implementation Stats --}}
        <x-shared.section 
            title="Implementation Statistics"
            description="Shared components system metrics"
            icon="heroicon-o-chart-bar"
        >
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <x-shared.card class="text-center">
                    <x-shared.badge variant="primary" size="lg" class="text-2xl font-bold px-4 py-2">
                        35+
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Shared Components</p>
                </x-shared.card>

                <x-shared.card class="text-center">
                    <x-shared.badge variant="success" size="lg" class="text-2xl font-bold px-4 py-2">
                        60%
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Code Reduction</p>
                </x-shared.card>

                <x-shared.card class="text-center">
                    <x-shared.badge variant="warning" size="lg" class="text-2xl font-bold px-4 py-2">
                        95+
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Translation Keys</p>
                </x-shared.card>

                <x-shared.card class="text-center">
                    <x-shared.badge variant="info" size="lg" class="text-2xl font-bold px-4 py-2">
                        100%
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Frontend Operational</p>
                </x-shared.card>
            </div>
        </x-shared.section>
    </div>
</div>
