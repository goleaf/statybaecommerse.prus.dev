@extends('components.layouts.base', ['title' => __('Campaigns')])

@section('content')
    <x-container class="py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ __('Campaigns') }}</h1>
            <a href="{{ route('admin.campaigns.create') }}"
               class="px-4 py-2 bg-primary-600 text-white rounded">{{ __('Create') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-2 pr-4">#</th>
                        <th class="py-2 pr-4">{{ __('Name') }}</th>
                        <th class="py-2 pr-4">{{ __('Slug') }}</th>
                        <th class="py-2 pr-4">{{ __('Status') }}</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $c)
                        <tr class="border-b">
                            <td class="py-2 pr-4">{{ $c['id'] }}</td>
                            <td class="py-2 pr-4">{{ $c['name'] }}</td>
                            <td class="py-2 pr-4">{{ $c['slug'] }}</td>
                            <td class="py-2 pr-4">{{ $c['status'] }}</td>
                            <td class="py-2 pr-4">
                                <a href="{{ route('admin.campaigns.edit', ['id' => $c['id']]) }}"
                                   class="text-primary-600 hover:underline">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-container>
@endsection
