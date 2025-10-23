@php
    $columns = $getColumns();
    $rows = $getRows();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="fi-fo-checkbox-matrix overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-300">
                        &nbsp;
                    </th>
                    @foreach ($columns as $columnKey => $columnLabel)
                        <th scope="col" class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-300">
                            {{ $columnLabel }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($rows as $rowKey => $rowLabel)
                    <tr wire:key="{{ $statePath }}.row.{{ $rowKey }}">
                        <th scope="row" class="px-3 py-2 text-start font-medium text-gray-700 dark:text-gray-200">
                            {{ $rowLabel }}
                        </th>
                        @foreach ($columns as $columnKey => $_columnLabel)
                            <td class="px-3 py-2 text-center">
                                <input
                                    type="checkbox"
                                    value="1"
                                    wire:key="{{ $statePath }}.row.{{ $rowKey }}.column.{{ $columnKey }}"
                                    wire:loading.attr="disabled"
                                    {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}.{{ $rowKey }}.{{ $columnKey }}"
                                    aria-label="{{ $rowLabel }} &ndash; {{ $columns[$columnKey] }}"
                                    @disabled($isDisabled)
                                    class="fi-checkbox-input"
                                />
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dynamic-component>
