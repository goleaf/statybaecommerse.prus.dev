<div class="mt-8">
    <h2 class="text-lg font-semibold">{{ __('Write a review') }}</h2>

    @guest
        <p class="text-sm text-gray-500 mt-2">{{ __('You must be logged in to write a review.') }}</p>
    @endguest

    @auth
        <div class="mt-4 space-y-3">
            <div>
                <label class="text-sm font-medium">{{ __('Title') }}</label>
                <input type="text" wire:model.defer="title" class="w-full rounded-md border-gray-300" />
                @error('title')
                    <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="text-sm font-medium">{{ __('Content') }}</label>
                <textarea wire:model.defer="content" rows="4" class="w-full rounded-md border-gray-300"></textarea>
                @error('content')
                    <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="text-sm font-medium">{{ __('Rating') }}</label>
                <select wire:model.defer="rating" class="rounded-md border-gray-300">
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
                @error('rating')
                    <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>
            <x-buttons.primary wire:click="submit"
                               wire:loading.attr="disabled">{{ __('Submit review') }}</x-buttons.primary>
        </div>
    @endauth
</div>
