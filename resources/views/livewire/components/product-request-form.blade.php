<div>
    @if (session('request_success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('request_success') }}
        </div>
    @endif

    @if ($product->is_requestable)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-blue-900">
                        {{ __('frontend.product.request_info_title') }}
                    </h3>
                    <p class="text-blue-700 mt-1">
                        {{ __('frontend.product.request_info_description') }}
                    </p>
                    @if ($product->request_message)
                        <p class="text-blue-600 mt-2 text-sm">
                            {{ $product->request_message }}
                        </p>
                    @endif
                </div>
                <button 
                    wire:click="toggleForm"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                >
                    {{ $showForm ? __('frontend.product.cancel_request') : __('frontend.product.request_product') }}
                </button>
            </div>
        </div>

        @if ($showForm)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">
                    {{ __('frontend.product.request_form_title') }}
                </h4>

                <form wire:submit.prevent="submitRequest">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('frontend.product.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name"
                                wire:model="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                required
                            >
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('frontend.product.email') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email"
                                wire:model="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                required
                            >
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('frontend.product.phone') }}
                            </label>
                            <input 
                                type="tel" 
                                id="phone"
                                wire:model="phone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                            >
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="requested_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('frontend.product.requested_quantity') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                id="requested_quantity"
                                wire:model="requested_quantity"
                                min="1"
                                max="999"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('requested_quantity') border-red-500 @enderror"
                                required
                            >
                            @error('requested_quantity')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('frontend.product.message') }}
                        </label>
                        <textarea 
                            id="message"
                            wire:model="message"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('message') border-red-500 @enderror"
                            placeholder="{{ __('frontend.product.message_placeholder') }}"
                        ></textarea>
                        @error('message')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button 
                            type="button"
                            wire:click="toggleForm"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        >
                            {{ __('frontend.product.cancel') }}
                        </button>
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        >
                            {{ __('frontend.product.submit_request') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>

