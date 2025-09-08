@php($layout = 'layouts.templates.app')

<x-dynamic-component :component="$layout">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold mb-6">{{ __('Exports') }}</h1>

        @if (session('error'))
            <div class="mb-4 rounded bg-red-50 p-3 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        @if(empty($files))
            <p class="text-slate-600">{{ __('No export files found.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('File') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Size (KB)') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($files as $file)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $file['name'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($file['size'] / 1024, 2) }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <x-link :href="route('exports.download', ['filename' => $file['name']])" class="text-primary-600">{{ __('Download') }}</x-link>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-dynamic-component>


