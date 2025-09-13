@props([
    'title' => null,
    'subtitle' => null,
    'showMap' => true,
    'showContactInfo' => true,
    'showBusinessHours' => true,
])

@php
    $title = $title ?? __('Contact Us');
    $subtitle = $subtitle ?? __('Get in touch with us. We\'d love to hear from you.');
@endphp

<div class="contact-form" x-data="contactForm()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $subtitle }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Contact Form --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Send us a Message') }}</h2>

                <form @submit.prevent="submitForm()" class="space-y-6">
                    {{-- Name and Email --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Full Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   x-model="form.name"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Email Address') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   x-model="form.email"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500">
                        </div>
                    </div>

                    {{-- Phone and Subject --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Phone Number') }}
                            </label>
                            <input type="tel"
                                   id="phone"
                                   x-model="form.phone"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500">
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Subject') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="subject"
                                    x-model="form.subject"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900">
                                <option value="">{{ __('Select a subject') }}</option>
                                <option value="general">{{ __('General Inquiry') }}</option>
                                <option value="support">{{ __('Technical Support') }}</option>
                                <option value="sales">{{ __('Sales Question') }}</option>
                                <option value="billing">{{ __('Billing Issue') }}</option>
                                <option value="partnership">{{ __('Partnership') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Message') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea id="message"
                                  x-model="form.message"
                                  rows="6"
                                  required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500"
                                  placeholder="{{ __('Please describe your inquiry in detail...') }}"></textarea>
                    </div>

                    {{-- Privacy Consent --}}
                    <div class="flex items-start gap-3">
                        <input type="checkbox"
                               id="privacy"
                               x-model="form.privacyConsent"
                               required
                               class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="privacy" class="text-sm text-gray-700">
                            {{ __('I agree to the') }}
                            <a href="{{ route('privacy', ['locale' => app()->getLocale()]) ?? '/privacy' }}"
                               class="text-blue-600 hover:text-blue-700 underline">
                                {{ __('Privacy Policy') }}
                            </a>
                            {{ __('and consent to the processing of my personal data.') }}
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            :disabled="loading"
                            class="w-full btn-gradient py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">{{ __('Send Message') }}</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            {{ __('Sending...') }}
                        </span>
                    </button>
                </form>

                {{-- Success/Error Messages --}}
                <div x-show="message"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="mt-6 p-4 rounded-xl"
                     :class="messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' :
                         'bg-red-50 text-red-800 border border-red-200'"
                     style="display: none;">
                    <div class="flex items-center gap-2">
                        <svg x-show="messageType === 'success'" class="w-5 h-5 text-green-600" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <svg x-show="messageType === 'error'" class="w-5 h-5 text-red-600" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span x-text="message"></span>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="space-y-8">
                {{-- Contact Details --}}
                @if ($showContactInfo)
                    <div class="bg-white border border-gray-200 rounded-2xl p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Get in Touch') }}</h2>

                        <div class="space-y-6">
                            {{-- Address --}}
                            <div class="flex items-start gap-4">
                                <div
                                     class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Address') }}</h3>
                                    <p class="text-gray-600">
                                        {{ app_setting('company_address') ?? __('123 Business Street, City, Country') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div class="flex items-start gap-4">
                                <div
                                     class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 011.21-.502l1.13 2.257a11.042 11.042 0 005.516-5.516l-2.257-1.13a1 1 0 01-.502-1.21l1.498-4.493A1 1 0 0118.72 3H21a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Phone') }}</h3>
                                    <p class="text-gray-600">
                                        <a href="tel:{{ app_setting('company_phone') ?? '+1 (555) 123-4567' }}"
                                           class="hover:text-blue-600 transition-colors duration-200">
                                            {{ app_setting('company_phone') ?? '+1 (555) 123-4567' }}
                                        </a>
                                    </p>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="flex items-start gap-4">
                                <div
                                     class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Email') }}</h3>
                                    <p class="text-gray-600">
                                        <a href="mailto:{{ app_setting('company_email') ?? 'info@example.com' }}"
                                           class="hover:text-blue-600 transition-colors duration-200">
                                            {{ app_setting('company_email') ?? 'info@example.com' }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Business Hours --}}
                @if ($showBusinessHours)
                    <div class="bg-white border border-gray-200 rounded-2xl p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Business Hours') }}</h2>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('Monday - Friday') }}</span>
                                <span class="font-medium text-gray-900">{{ __('9:00 AM - 6:00 PM') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('Saturday') }}</span>
                                <span class="font-medium text-gray-900">{{ __('10:00 AM - 4:00 PM') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('Sunday') }}</span>
                                <span class="font-medium text-gray-900">{{ __('Closed') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Map --}}
                @if ($showMap)
                    <div class="bg-white border border-gray-200 rounded-2xl p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Find Us') }}</h2>
                        <div class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                                    </path>
                                </svg>
                                <p class="text-gray-500">{{ __('Interactive map will be displayed here') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function contactForm() {
        return {
            loading: false,
            message: '',
            messageType: 'success',
            form: {
                name: '',
                email: '',
                phone: '',
                subject: '',
                message: '',
                privacyConsent: false
            },

            async submitForm() {
                if (!this.form.privacyConsent) {
                    this.showMessage('{{ __('Please accept the privacy policy to continue.') }}', 'error');
                    return;
                }

                this.loading = true;
                this.message = '';

                try {
                    const response = await fetch('/contact', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showMessage('{{ __('Thank you for your message! We will get back to you soon.') }}',
                            'success');
                        this.resetForm();
                    } else {
                        this.showMessage(data.message || '{{ __('Something went wrong. Please try again.') }}',
                            'error');
                    }
                } catch (error) {
                    this.showMessage('{{ __('Network error. Please check your connection and try again.') }}',
                        'error');
                } finally {
                    this.loading = false;
                }
            },

            showMessage(text, type) {
                this.message = text;
                this.messageType = type;

                // Clear message after 5 seconds
                setTimeout(() => {
                    this.message = '';
                }, 5000);
            },

            resetForm() {
                this.form = {
                    name: '',
                    email: '',
                    phone: '',
                    subject: '',
                    message: '',
                    privacyConsent: false
                };
            }
        }
    }
</script>

