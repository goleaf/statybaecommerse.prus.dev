<x-layouts.templates.account title="{{ __('referrals.create.title') }}">
    <div class="space-y-6">
        <header class="border-b border-gray-200 pb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('referrals.create.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('referrals.create.description') }}</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <form action="{{ route('referrals.store') }}" method="POST" class="space-y-5 rounded-lg border border-gray-200 p-6">
                    @csrf

                    <div>
                        <label for="referred_email" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('referrals.create.fields.referred_email') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="referred_email"
                            name="referred_email"
                            value="{{ old('referred_email') }}"
                            required
                            class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ __('referrals.create.fields.referred_email_placeholder') }}"
                        >
                        @error('referred_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('referrals.create.fields.title') }}
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ __('referrals.create.fields.title_placeholder') }}"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('referrals.create.fields.description') }}
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ __('referrals.create.fields.description_placeholder') }}"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <label class="inline-flex items-start gap-2 text-sm text-gray-700" for="terms">
                            <input
                                id="terms"
                                name="terms"
                                type="checkbox"
                                required
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                            <span>
                                {{ __('referrals.create.terms.prefix') }}
                                <a href="{{ route('frontend.legal.terms') }}" class="font-medium text-primary-700 hover:text-primary-800">
                                    {{ __('referrals.create.terms.link') }}
                                </a>
                            </span>
                        </label>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-md bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
                            {{ __('referrals.create.submit') }}
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="rounded-lg border border-gray-200 p-5">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.create.why_title') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li>{{ __('referrals.create.why_items.1') }}</li>
                        <li>{{ __('referrals.create.why_items.2') }}</li>
                        <li>{{ __('referrals.create.why_items.3') }}</li>
                    </ul>
                </div>

                <div class="rounded-lg border border-gray-200 p-5">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.create.how_title') }}</h2>
                    <ol class="mt-3 list-decimal space-y-2 pl-4 text-sm text-gray-600">
                        <li>{{ __('referrals.create.how_items.1') }}</li>
                        <li>{{ __('referrals.create.how_items.2') }}</li>
                        <li>{{ __('referrals.create.how_items.3') }}</li>
                    </ol>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.templates.account>
