{{-- Mobile-Responsive Form Component for Filament Admin --}}
@props([
    'title' => '',
    'description' => '',
    'sections' => [],
    'actions' => [],
    'sticky' => true,
])

<div class="fi-mobile-form-container">
    {{-- Mobile Form Header --}}
    <div class="fi-mobile-form-header bg-white border-b border-gray-200 p-4 lg:hidden {{ $sticky ? 'sticky top-0 z-10' : '' }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button 
                    type="button"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-md lg:hidden"
                    onclick="history.back()"
                    aria-label="{{ __('admin.form.go_back') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
                    @if($description)
                        <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
                    @endif
                </div>
            </div>
            
            {{-- Mobile Form Actions --}}
            <div class="flex items-center space-x-2">
                @foreach($actions as $action)
                    @if($action['primary'] ?? false)
                        <button 
                            type="{{ $action['type'] ?? 'button' }}"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ isset($action['disabled']) && $action['disabled'] ? 'disabled' : '' }}
                        >
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
                
                {{-- Mobile Menu Toggle --}}
                <button 
                    type="button"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-md"
                    onclick="toggleMobileFormMenu()"
                    aria-label="{{ __('admin.form.toggle_menu') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Form Menu --}}
        <div id="mobile-form-menu" class="hidden mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-2">
                @foreach($actions as $action)
                    @if(!($action['primary'] ?? false))
                        <button 
                            type="{{ $action['type'] ?? 'button' }}"
                            class="px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ isset($action['disabled']) && $action['disabled'] ? 'disabled' : '' }}
                        >
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Desktop Form (hidden on mobile) --}}
    <div class="hidden lg:block">
        {{ $slot }}
    </div>

    {{-- Mobile Form Content --}}
    <div class="lg:hidden">
        <div class="space-y-6 p-4">
            @if(count($sections) > 0)
                @foreach($sections as $section)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        {{-- Section Header --}}
                        @if(isset($section['title']) || isset($section['description']))
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                @if(isset($section['title']))
                                    <h3 class="text-base font-medium text-gray-900">{{ $section['title'] }}</h3>
                                @endif
                                @if(isset($section['description']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $section['description'] }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Section Content --}}
                        <div class="p-4 space-y-4">
                            @if(isset($section['fields']))
                                @foreach($section['fields'] as $field)
                                    <div class="fi-mobile-form-field">
                                        {{-- Field Label --}}
                                        @if(isset($field['label']))
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                {{ $field['label'] }}
                                                @if($field['required'] ?? false)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                        @endif

                                        {{-- Field Input --}}
                                        @switch($field['type'] ?? 'text')
                                            @case('text')
                                            @case('email')
                                            @case('password')
                                            @case('number')
                                                <input 
                                                    type="{{ $field['type'] }}"
                                                    name="{{ $field['name'] }}"
                                                    id="{{ $field['name'] }}"
                                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base"
                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                    value="{{ $field['value'] ?? '' }}"
                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                    {{ ($field['disabled'] ?? false) ? 'disabled' : '' }}
                                                >
                                                @break

                                            @case('textarea')
                                                <textarea 
                                                    name="{{ $field['name'] }}"
                                                    id="{{ $field['name'] }}"
                                                    rows="{{ $field['rows'] ?? 4 }}"
                                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base resize-y"
                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                    {{ ($field['disabled'] ?? false) ? 'disabled' : '' }}
                                                >{{ $field['value'] ?? '' }}</textarea>
                                                @break

                                            @case('select')
                                                <select 
                                                    name="{{ $field['name'] }}"
                                                    id="{{ $field['name'] }}"
                                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base"
                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                    {{ ($field['disabled'] ?? false) ? 'disabled' : '' }}
                                                >
                                                    @if(isset($field['placeholder']))
                                                        <option value="">{{ $field['placeholder'] }}</option>
                                                    @endif
                                                    @if(isset($field['options']))
                                                        @foreach($field['options'] as $value => $label)
                                                            <option 
                                                                value="{{ $value }}"
                                                                {{ ($field['value'] ?? '') == $value ? 'selected' : '' }}
                                                            >
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @break

                                            @case('checkbox')
                                                <div class="flex items-center">
                                                    <input 
                                                        type="checkbox"
                                                        name="{{ $field['name'] }}"
                                                        id="{{ $field['name'] }}"
                                                        class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                        value="{{ $field['checkbox_value'] ?? '1' }}"
                                                        {{ ($field['checked'] ?? false) ? 'checked' : '' }}
                                                        {{ ($field['disabled'] ?? false) ? 'disabled' : '' }}
                                                    >
                                                    @if(isset($field['checkbox_label']))
                                                        <label for="{{ $field['name'] }}" class="ml-3 text-sm text-gray-700">
                                                            {{ $field['checkbox_label'] }}
                                                        </label>
                                                    @endif
                                                </div>
                                                @break

                                            @case('radio')
                                                @if(isset($field['options']))
                                                    <div class="space-y-3">
                                                        @foreach($field['options'] as $value => $label)
                                                            <div class="flex items-center">
                                                                <input 
                                                                    type="radio"
                                                                    name="{{ $field['name'] }}"
                                                                    id="{{ $field['name'] }}_{{ $value }}"
                                                                    class="h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                                                                    value="{{ $value }}"
                                                                    {{ ($field['value'] ?? '') == $value ? 'checked' : '' }}
                                                                    {{ ($field['disabled'] ?? false) ? 'disabled' : '' }}
                                                                >
                                                                <label for="{{ $field['name'] }}_{{ $value }}" class="ml-3 text-sm text-gray-700">
                                                                    {{ $label }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @break

                                            @case('file')
                                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                                                    <input 
                                                        type="file"
                                                        name="{{ $field['name'] }}"
                                                        id="{{ $field['name'] }}"
                                                        class="hidden"
                                                        {{ isset($field['accept']) ? 'accept=' . $field['accept'] : '' }}
                                                        {{ ($field['multiple'] ?? false) ? 'multiple' : '' }}
                                                        {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                        {{ ($field['disabled'] ?? false) ? 'disabled' : '' }}
                                                        onchange="updateFileLabel(this)"
                                                    >
                                                    <label for="{{ $field['name'] }}" class="cursor-pointer">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        <div class="mt-4">
                                                            <p class="text-sm text-gray-600">
                                                                <span class="font-medium text-blue-600 hover:text-blue-500">{{ __('admin.form.click_to_upload') }}</span>
                                                                {{ __('admin.form.or_drag_and_drop') }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1" id="{{ $field['name'] }}_label">
                                                                {{ $field['help'] ?? __('admin.form.file_help') }}
                                                            </p>
                                                        </div>
                                                    </label>
                                                </div>
                                                @break
                                        @endswitch

                                        {{-- Field Help Text --}}
                                        @if(isset($field['help']) && $field['type'] !== 'file')
                                            <p class="text-xs text-gray-500 mt-1">{{ $field['help'] }}</p>
                                        @endif

                                        {{-- Field Error --}}
                                        @if(isset($field['error']))
                                            <p class="text-xs text-red-600 mt-1">{{ $field['error'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Default slot content for mobile --}}
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    {{ $slot }}
                </div>
            @endif
        </div>

        {{-- Mobile Form Footer --}}
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 lg:hidden">
            <div class="flex space-x-3">
                @foreach($actions as $action)
                    @if($action['primary'] ?? false)
                        <button 
                            type="{{ $action['type'] ?? 'button' }}"
                            class="flex-1 px-4 py-3 bg-blue-600 text-white text-base font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ isset($action['disabled']) && $action['disabled'] ? 'disabled' : '' }}
                        >
                            {{ $action['label'] }}
                        </button>
                    @else
                        <button 
                            type="{{ $action['type'] ?? 'button' }}"
                            class="px-4 py-3 border border-gray-300 text-gray-700 text-base font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ isset($action['disabled']) && $action['disabled'] ? 'disabled' : '' }}
                        >
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Mobile Form JavaScript --}}
<script>
function toggleMobileFormMenu() {
    const menu = document.getElementById('mobile-form-menu');
    menu.classList.toggle('hidden');
}

function updateFileLabel(input) {
    const label = document.getElementById(input.id + '_label');
    if (input.files && input.files.length > 0) {
        if (input.files.length === 1) {
            label.textContent = input.files[0].name;
        } else {
            label.textContent = `${input.files.length} {{ __('admin.form.files_selected') }}`;
        }
    } else {
        label.textContent = '{{ __('admin.form.file_help') }}';
    }
}

// Auto-resize textareas
document.addEventListener('input', function(event) {
    if (event.target.tagName === 'TEXTAREA') {
        event.target.style.height = 'auto';
        event.target.style.height = event.target.scrollHeight + 'px';
    }
});

// Close mobile form menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('mobile-form-menu');
    const toggle = event.target.closest('[onclick*="toggleMobileFormMenu"]');
    
    if (!menu.contains(event.target) && !toggle) {
        menu.classList.add('hidden');
    }
});

// Handle form validation on mobile
document.addEventListener('invalid', function(event) {
    event.preventDefault();
    
    // Scroll to first invalid field
    const firstInvalid = document.querySelector(':invalid');
    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();
    }
}, true);

// Prevent zoom on iOS when focusing inputs
if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (input.style.fontSize !== '16px') {
            input.style.fontSize = '16px';
        }
    });
}
</script>

{{-- Mobile Form Styles --}}
<style>
.fi-mobile-form-container {
    -webkit-overflow-scrolling: touch;
}

.fi-mobile-form-container input,
.fi-mobile-form-container select,
.fi-mobile-form-container textarea,
.fi-mobile-form-container button {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.fi-mobile-form-container button:active {
    transform: scale(0.98);
}

/* Improve touch targets */
@media (hover: none) and (pointer: coarse) {
    .fi-mobile-form-container input,
    .fi-mobile-form-container select,
    .fi-mobile-form-container textarea,
    .fi-mobile-form-container button {
        min-height: 44px;
    }
    
    .fi-mobile-form-container input[type="checkbox"],
    .fi-mobile-form-container input[type="radio"] {
        min-width: 44px;
        min-height: 44px;
        transform: scale(1.5);
        margin: 8px;
    }
}

/* Smooth transitions */
.fi-mobile-form-container * {
    transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.1s ease;
}

/* Auto-resize textareas */
.fi-mobile-form-container textarea {
    resize: none;
    overflow: hidden;
}

/* File upload drag and drop */
.fi-mobile-form-container input[type="file"] + label {
    transition: border-color 0.2s ease, background-color 0.2s ease;
}

.fi-mobile-form-container input[type="file"]:focus + label {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

/* Mobile form spacing */
@media (max-width: 768px) {
    .fi-mobile-form-container {
        padding-bottom: 80px; /* Space for sticky footer */
    }
}
</style>