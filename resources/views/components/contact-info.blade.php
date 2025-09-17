@props([
    'showAddress' => true,
    'showPhone' => true,
    'showEmail' => true,
    'showHours' => true,
    'variant' => 'default', // default, compact, detailed
])

@php
    $isCompact = $variant === 'compact';
    $isDetailed = $variant === 'detailed';
@endphp

<div class="contact-info {{ $isCompact ? 'space-y-3' : 'space-y-6' }}">
    {{-- Address --}}
    @if ($showAddress)
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 {{ $isCompact ? 'mt-0.5' : 'mt-1' }}">
                <svg class="{{ $isCompact ? 'w-4 h-4' : 'w-5 h-5' }} text-blue-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div>
                <h3
                    class="{{ $isCompact ? 'text-sm font-medium text-gray-900' : 'text-base font-semibold text-gray-900' }}">
                    {{ __('Address') }}</h3>
                <p class="{{ $isCompact ? 'text-sm text-gray-600' : 'text-gray-600' }}">
                    {{ app_setting('company_address') ?? __('123 Business Street, City, Country') }}
                </p>
                @if ($isDetailed)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Get directions') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Phone --}}
    @if ($showPhone)
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 {{ $isCompact ? 'mt-0.5' : 'mt-1' }}">
                <svg class="{{ $isCompact ? 'w-4 h-4' : 'w-5 h-5' }} text-blue-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                    </path>
                </svg>
            </div>
            <div>
                <h3
                    class="{{ $isCompact ? 'text-sm font-medium text-gray-900' : 'text-base font-semibold text-gray-900' }}">
                    {{ __('Phone') }}</h3>
                <a href="tel:{{ app_setting('company_phone') ?? '+370 123 45678' }}"
                   class="{{ $isCompact ? 'text-sm text-blue-600 hover:text-blue-700' : 'text-blue-600 hover:text-blue-700' }} transition-colors duration-200">
                    {{ app_setting('company_phone') ?? '+370 123 45678' }}
                </a>
                @if ($isDetailed)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Call us for support') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Email --}}
    @if ($showEmail)
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 {{ $isCompact ? 'mt-0.5' : 'mt-1' }}">
                <svg class="{{ $isCompact ? 'w-4 h-4' : 'w-5 h-5' }} text-blue-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
            <div>
                <h3
                    class="{{ $isCompact ? 'text-sm font-medium text-gray-900' : 'text-base font-semibold text-gray-900' }}">
                    {{ __('Email') }}</h3>
                <a href="mailto:{{ app_setting('company_email') ?? 'info@example.com' }}"
                   class="{{ $isCompact ? 'text-sm text-blue-600 hover:text-blue-700' : 'text-blue-600 hover:text-blue-700' }} transition-colors duration-200">
                    {{ app_setting('company_email') ?? 'info@example.com' }}
                </a>
                @if ($isDetailed)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('We respond within 24 hours') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Business Hours --}}
    @if ($showHours)
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 {{ $isCompact ? 'mt-0.5' : 'mt-1' }}">
                <svg class="{{ $isCompact ? 'w-4 h-4' : 'w-5 h-5' }} text-blue-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3
                    class="{{ $isCompact ? 'text-sm font-medium text-gray-900' : 'text-base font-semibold text-gray-900' }}">
                    {{ __('Business Hours') }}</h3>
                <div class="{{ $isCompact ? 'text-sm text-gray-600' : 'text-gray-600' }}">
                    <p>{{ __('Monday - Friday') }}:
                        {{ app_setting('business_hours_weekdays') ?? '9:00 AM - 6:00 PM' }}</p>
                    <p>{{ __('Saturday') }}: {{ app_setting('business_hours_saturday') ?? '10:00 AM - 4:00 PM' }}</p>
                    <p>{{ __('Sunday') }}: {{ app_setting('business_hours_sunday') ?? __('Closed') }}</p>
                </div>
                @if ($isDetailed)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Customer support available during business hours') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Additional Contact Methods (Detailed variant only) --}}
    @if ($isDetailed)
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('Other Ways to Reach Us') }}</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Live Chat --}}
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ __('Live Chat') }}</h4>
                        <p class="text-xs text-gray-600">{{ __('Available 24/7') }}</p>
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path
                              d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ __('WhatsApp') }}</h4>
                        <p class="text-xs text-gray-600">{{ app_setting('whatsapp_number') ?? '+370 123 45678' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

