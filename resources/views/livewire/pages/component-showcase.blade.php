@section('meta')
    <x-meta
        title="{{ __('frontend.component_showcase.meta_title', ['app' => config('app.name')]) }}"
        description="{{ __('frontend.component_showcase.meta_description') }}"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Page Header --}}
    <x-shared.page-header
        title="{{ __('frontend.component_showcase.title') }}"
        description="{{ __('frontend.component_showcase.description') }}"
        icon="heroicon-o-squares-2x2"
        :breadcrumbs="[
            ['title' => __('shared.home'), 'url' => route('home')],
            ['title' => __('frontend.component_showcase.breadcrumb')]
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Buttons Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.buttons.title') }}"
            description="{{ __('frontend.component_showcase.sections.buttons.description') }}"
            icon="heroicon-o-cursor-arrow-rays"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Button Variants --}}
                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">{{ __('frontend.component_showcase.buttons.variants') }}</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.button variant="primary">{{ __('frontend.component_showcase.buttons.primary') }}</x-shared.button>
                        <x-shared.button variant="secondary">{{ __('frontend.component_showcase.buttons.secondary') }}</x-shared.button>
                        <x-shared.button variant="success">{{ __('frontend.component_showcase.buttons.success') }}</x-shared.button>
                        <x-shared.button variant="warning">{{ __('frontend.component_showcase.buttons.warning') }}</x-shared.button>
                        <x-shared.button variant="danger">{{ __('frontend.component_showcase.buttons.danger') }}</x-shared.button>
                        <x-shared.button variant="ghost">{{ __('frontend.component_showcase.buttons.ghost') }}</x-shared.button>
                    </div>
                </x-shared.card>

                {{-- Button Sizes --}}
                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">{{ __('frontend.component_showcase.buttons.sizes') }}</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.button variant="primary" size="sm">{{ __('frontend.component_showcase.buttons.size_small') }}</x-shared.button>
                        <x-shared.button variant="primary" size="md">{{ __('frontend.component_showcase.buttons.size_medium') }}</x-shared.button>
                        <x-shared.button variant="primary" size="lg">{{ __('frontend.component_showcase.buttons.size_large') }}</x-shared.button>
                        <x-shared.button variant="primary" size="xl">{{ __('frontend.component_showcase.buttons.size_extra_large') }}</x-shared.button>
                    </div>
                </x-shared.card>
            </div>
        </x-shared.section>

        {{-- Badges Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.badges.title') }}"
            description="{{ __('frontend.component_showcase.sections.badges.description') }}"
            icon="heroicon-o-tag"
        >
            <x-shared.card>
                <div class="flex flex-wrap gap-4">
                    <x-shared.badge variant="primary">{{ __('frontend.component_showcase.badges.primary') }}</x-shared.badge>
                    <x-shared.badge variant="secondary">{{ __('frontend.component_showcase.badges.secondary') }}</x-shared.badge>
                    <x-shared.badge variant="success">{{ __('frontend.component_showcase.badges.success') }}</x-shared.badge>
                    <x-shared.badge variant="warning">{{ __('frontend.component_showcase.badges.warning') }}</x-shared.badge>
                    <x-shared.badge variant="danger">{{ __('frontend.component_showcase.badges.danger') }}</x-shared.badge>
                    <x-shared.badge variant="info">{{ __('frontend.component_showcase.badges.info') }}</x-shared.badge>
                    <x-shared.badge variant="gray">{{ __('frontend.component_showcase.badges.gray') }}</x-shared.badge>
                </div>
            </x-shared.card>
        </x-shared.section>

        {{-- Form Components Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.forms.title') }}"
            description="{{ __('frontend.component_showcase.sections.forms.description') }}"
            icon="heroicon-o-document-text"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">{{ __('frontend.component_showcase.forms.input_components') }}</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.input 
                            wire:model="testInput"
                            label="{{ __('frontend.component_showcase.forms.test_input_label') }}"
                            placeholder="{{ __('frontend.component_showcase.forms.test_input_placeholder') }}"
                            help-text="{{ __('frontend.component_showcase.forms.test_input_help') }}"
                        />
                        
                        <x-shared.input 
                            type="email"
                            label="{{ __('frontend.component_showcase.forms.email_input_label') }}"
                            placeholder="{{ __('frontend.component_showcase.forms.email_input_placeholder') }}"
                            icon="heroicon-o-envelope"
                        />
                        
                        <x-shared.input 
                            type="search"
                            label="{{ __('frontend.component_showcase.forms.search_input_label') }}"
                            placeholder="{{ __('frontend.component_showcase.forms.search_input_placeholder') }}"
                            icon="heroicon-o-magnifying-glass"
                        />
                    </div>
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold">{{ __('frontend.component_showcase.forms.select_components') }}</h3>
                    </x-slot>
                    
                    <div class="space-y-4">
                        <x-shared.select 
                            wire:model="testSelect"
                            label="{{ __('frontend.component_showcase.forms.test_select_label') }}"
                            placeholder="{{ __('frontend.component_showcase.forms.test_select_placeholder') }}"
                        >
                            <option value="option1">{{ __('frontend.component_showcase.forms.option_one') }}</option>
                            <option value="option2">{{ __('frontend.component_showcase.forms.option_two') }}</option>
                            <option value="option3">{{ __('frontend.component_showcase.forms.option_three') }}</option>
                        </x-shared.select>
                    </div>
                </x-shared.card>
            </div>
        </x-shared.section>

        {{-- Notifications Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.notifications.title') }}"
            description="{{ __('frontend.component_showcase.sections.notifications.description') }}"
            icon="heroicon-o-bell"
        >
            <x-shared.card>
                <div class="flex flex-wrap gap-4">
                    <x-shared.button 
                        wire:click="testNotification('success')"
                        variant="success"
                        size="sm"
                    >
                        {{ __('frontend.component_showcase.notifications.test_success') }}
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="testNotification('error')"
                        variant="danger"
                        size="sm"
                    >
                        {{ __('frontend.component_showcase.notifications.test_error') }}
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="testNotification('warning')"
                        variant="warning"
                        size="sm"
                    >
                        {{ __('frontend.component_showcase.notifications.test_warning') }}
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="testNotification('info')"
                        variant="secondary"
                        size="sm"
                    >
                        {{ __('frontend.component_showcase.notifications.test_info') }}
                    </x-shared.button>
                </div>
            </x-shared.card>
        </x-shared.section>

        {{-- Loading States Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.loading.title') }}"
            description="{{ __('frontend.component_showcase.sections.loading.description') }}"
            icon="heroicon-o-arrow-path"
        >
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">{{ __('frontend.component_showcase.loading.spinner') }}</h4>
                    </x-slot>
                    <x-shared.loading type="spinner" size="md" text="{{ __('frontend.component_showcase.loading.loading') }}" />
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">{{ __('frontend.component_showcase.loading.skeleton') }}</h4>
                    </x-slot>
                    <x-shared.loading type="skeleton" size="md" />
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">{{ __('frontend.component_showcase.loading.pulse') }}</h4>
                    </x-slot>
                    <x-shared.loading type="pulse" size="md" />
                </x-shared.card>

                <x-shared.card>
                    <x-slot name="header">
                        <h4 class="font-medium">{{ __('frontend.component_showcase.loading.dots') }}</h4>
                    </x-slot>
                    <x-shared.loading type="dots" text="{{ __('frontend.component_showcase.loading.processing') }}" />
                </x-shared.card>
            </div>
        </x-shared.section>

        {{-- Product Components Section --}}
        @if($featuredProducts->isNotEmpty())
            <x-shared.section 
                title="{{ __('frontend.component_showcase.sections.products.title') }}"
                description="{{ __('frontend.component_showcase.sections.products.description') }}"
                icon="heroicon-o-cube"
            >
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($featuredProducts as $product)
                        @include('livewire.home.partials.product-card', [
                            'product' => $product,
                            'preset' => 'featured',
                            'attributes' => new \Illuminate\View\ComponentAttributeBag(['class' => 'h-full']),
                        ])
                    @endforeach
                </div>
            </x-shared.section>
        @endif

        {{-- Empty State Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.empty.title') }}"
            description="{{ __('frontend.component_showcase.sections.empty.description') }}"
            icon="heroicon-o-exclamation-triangle"
        >
            <x-shared.empty-state
                title="{{ __('frontend.component_showcase.empty_state.title') }}"
                description="{{ __('frontend.component_showcase.empty_state.description') }}"
                icon="heroicon-o-cube"
                action-text="{{ __('frontend.component_showcase.empty_state.action') }}"
                action-url="{{ route('frontend.products.index', []) }}"
            />
        </x-shared.section>

        {{-- Modal Section --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.modal.title') }}"
            description="{{ __('frontend.component_showcase.sections.modal.description') }}"
            icon="heroicon-o-window"
        >
            <x-shared.card>
                <x-shared.button 
                    wire:click="toggleModal"
                    wire:confirm="{{ __('translations.confirm_toggle_modal') }}"
                    variant="primary"
                >
                    {{ __('frontend.component_showcase.modal.open') }}
                </x-shared.button>

                <x-shared.modal 
                    title="{{ __('frontend.component_showcase.modal.title') }}"
                    :show="$showModal"
                    max-width="md"
                >
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ __('frontend.component_showcase.modal.description') }}
                    </p>
                    
                    <x-slot name="footer">
                        <x-shared.button 
                            wire:click="toggleModal"
                            wire:confirm="{{ __('translations.confirm_toggle_modal') }}"
                            variant="secondary"
                        >
                            {{ __('frontend.component_showcase.modal.close') }}
                        </x-shared.button>
                        
                        <x-shared.button variant="primary">
                            {{ __('frontend.component_showcase.modal.confirm') }}
                        </x-shared.button>
                    </x-slot>
                </x-shared.modal>
            </x-shared.card>
        </x-shared.section>

        {{-- Implementation Stats --}}
        <x-shared.section 
            title="{{ __('frontend.component_showcase.sections.stats.title') }}"
            description="{{ __('frontend.component_showcase.sections.stats.description') }}"
            icon="heroicon-o-chart-bar"
        >
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <x-shared.card class="text-center">
                    <x-shared.badge variant="primary" size="lg" class="text-2xl font-bold px-4 py-2">
                        35+
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('frontend.component_showcase.stats.shared_components') }}</p>
                </x-shared.card>

                <x-shared.card class="text-center">
                    <x-shared.badge variant="success" size="lg" class="text-2xl font-bold px-4 py-2">
                        60%
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('frontend.component_showcase.stats.code_reduction') }}</p>
                </x-shared.card>

                <x-shared.card class="text-center">
                    <x-shared.badge variant="warning" size="lg" class="text-2xl font-bold px-4 py-2">
                        95+
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('frontend.component_showcase.stats.translation_keys') }}</p>
                </x-shared.card>

                <x-shared.card class="text-center">
                    <x-shared.badge variant="info" size="lg" class="text-2xl font-bold px-4 py-2">
                        100%
                    </x-shared.badge>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('frontend.component_showcase.stats.frontend_operational') }}</p>
                </x-shared.card>
            </div>
        </x-shared.section>
    </div>
</div>


