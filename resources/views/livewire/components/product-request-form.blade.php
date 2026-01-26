<div class="space-y-4">
    @if (session('request_success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('request_success') }}
        </div>
    @endif

    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
        <div class="space-y-4 p-6 lg:p-8">
            <h2 class="text-base font-semibold text-slate-900">
                {{ __('messages.product_page) }}
            </h2>
            <p class="text-sm text-slate-600">
                {{ __('messages.product_page) }}
            </p>
            @if ($product->request_message)
                <p class="text-xs text-slate-500">
                    {{ $product->request_message }}
                </p>
            @endif
            <button
                type="button"
                wire:click="toggleForm"
                class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
            >
                <x-heroicon-o-phone class="mr-2 h-4 w-4" />
                {{ $showForm ? __('messages.frontend) : __('messages.translations) }}
            </button>
        </div>
    </section>

    @if ($showForm)
        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="p-6 lg:p-8">
                <h3 class="text-base font-semibold text-slate-900">
                    {{ __('frontend.product.request_form_title') }}
                </h3>

                <form wire:submit.prevent="submitRequest" class="mt-6 space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('messages.frontend) }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 @error('name') border-red-500 focus:ring-red-200 @enderror"
                                required
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('messages.frontend) }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                wire:model="email"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 @error('email') border-red-500 focus:ring-red-200 @enderror"
                                required
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('messages.frontend) }}
                            </label>
                            <input
                                type="tel"
                                id="phone"
                                wire:model="phone"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 @error('phone') border-red-500 focus:ring-red-200 @enderror"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="requested_quantity" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('frontend.product.requested_quantity') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                id="requested_quantity"
                                wire:model="requested_quantity"
                                min="1"
                                max="999"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 @error('requested_quantity') border-red-500 focus:ring-red-200 @enderror"
                                required
                            >
                            @error('requested_quantity')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="message" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('messages.frontend) }}
                        </label>
                        <textarea
                            id="message"
                            wire:model="message"
                            rows="4"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 @error('message') border-red-500 focus:ring-red-200 @enderror"
                            placeholder="{{ __('frontend.product.message_placeholder') }}"
                        ></textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="toggleForm"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                        >
                            {{ __('messages.frontend) }}
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                        >
                            {{ __('frontend.product.submit_request') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endif
</div>

