@extends('frontend.layouts.app')

@section('title', __('campaign_conversions.pages.edit.title'))
@section('description', __('campaign_conversions.pages.edit.description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('campaign_conversions.pages.edit.title') }}</h1>
                <p class="text-gray-600 mt-2">{{ __('campaign_conversions.pages.edit.description') }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('frontend.campaign-conversions.show', $campaignConversion) }}"
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    {{ __('campaign_conversions.actions.view') }}
                </a>
                <a href="{{ route('frontend.campaign-conversions.index') }}"
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    {{ __('campaign_conversions.actions.back_to_list') }}
                </a>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <form method="POST" action="{{ route('frontend.campaign-conversions.update', $campaignConversion) }}"
                  class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        {{ __('campaign_conversions.sections.basic_information') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="campaign_id" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.campaign') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="campaign_id" id="campaign_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('campaign_conversions.fields.select_campaign') }}</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}"
                                            {{ old('campaign_id', $campaignConversion->campaign_id) == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('campaign_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="conversion_type" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.conversion_type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="conversion_type" id="conversion_type" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('campaign_conversions.fields.select_type') }}</option>
                                @foreach ($conversionTypes as $key => $label)
                                    <option value="{{ $key }}"
                                            {{ old('conversion_type', $campaignConversion->conversion_type) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('conversion_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="conversion_value" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.conversion_value') }} <span
                                      class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">€</span>
                                </div>
                                <input type="number" name="conversion_value" id="conversion_value" required
                                       value="{{ old('conversion_value', $campaignConversion->conversion_value) }}"
                                       step="0.01" min="0"
                                       class="w-full pl-8 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @error('conversion_value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}"
                                            {{ old('status', $campaignConversion->status) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="converted_at" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.converted_at') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="converted_at" id="converted_at" required
                                   value="{{ old('converted_at', $campaignConversion->converted_at->format('Y-m-d\TH:i')) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('converted_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tracking Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        {{ __('campaign_conversions.sections.tracking_information') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.source') }}
                            </label>
                            <input type="text" name="source" id="source"
                                   value="{{ old('source', $campaignConversion->source) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('source')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="medium" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.medium') }}
                            </label>
                            <input type="text" name="medium" id="medium"
                                   value="{{ old('medium', $campaignConversion->medium) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('medium')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="campaign_name" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.campaign_name') }}
                            </label>
                            <input type="text" name="campaign_name" id="campaign_name"
                                   value="{{ old('campaign_name', $campaignConversion->campaign_name) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('campaign_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="utm_content" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.utm_content') }}
                            </label>
                            <input type="text" name="utm_content" id="utm_content"
                                   value="{{ old('utm_content', $campaignConversion->utm_content) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('utm_content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="utm_term" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.utm_term') }}
                            </label>
                            <input type="text" name="utm_term" id="utm_term"
                                   value="{{ old('utm_term', $campaignConversion->utm_term) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('utm_term')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="referrer" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.referrer') }}
                            </label>
                            <input type="url" name="referrer" id="referrer"
                                   value="{{ old('referrer', $campaignConversion->referrer) }}" maxlength="500"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('referrer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Device Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        {{ __('campaign_conversions.sections.device_information') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="device_type" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.device_type') }}
                            </label>
                            <select name="device_type" id="device_type"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('campaign_conversions.fields.select_device') }}</option>
                                @foreach ($deviceTypes as $key => $label)
                                    <option value="{{ $key }}"
                                            {{ old('device_type', $campaignConversion->device_type) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('device_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="browser" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.browser') }}
                            </label>
                            <input type="text" name="browser" id="browser"
                                   value="{{ old('browser', $campaignConversion->browser) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('browser')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="os" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.os') }}
                            </label>
                            <input type="text" name="os" id="os"
                                   value="{{ old('os', $campaignConversion->os) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('os')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.country') }}
                            </label>
                            <input type="text" name="country" id="country"
                                   value="{{ old('country', $campaignConversion->country) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.city') }}
                            </label>
                            <input type="text" name="city" id="city"
                                   value="{{ old('city', $campaignConversion->city) }}" maxlength="255"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        {{ __('campaign_conversions.sections.additional_information') }}</h2>

                    <div class="space-y-6">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.notes') }}
                            </label>
                            <textarea name="notes" id="notes" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $campaignConversion->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('campaign_conversions.fields.tags') }}
                            </label>
                            <input type="text" name="tags" id="tags"
                                   value="{{ old('tags', is_array($campaignConversion->tags) ? implode(', ', $campaignConversion->tags) : $campaignConversion->tags) }}"
                                   placeholder="{{ __('campaign_conversions.fields.tags_placeholder') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">{{ __('campaign_conversions.fields.tags_help') }}</p>
                            @error('tags')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('frontend.campaign-conversions.show', $campaignConversion) }}"
                       class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        {{ __('campaign_conversions.actions.cancel') }}
                    </a>
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('campaign_conversions.actions.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
