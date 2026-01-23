<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.news_images_table.title') }}</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; padding: 2rem; background-color: #f8fafc; color: #0f172a; }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: #475569; background-color: #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        .preview img { border-radius: 0.75rem; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.15); width: 80px; height: 80px; object-fit: cover; }
        .table-actions, .table-pagination, .filters { margin-top: 1rem; font-size: 0.95rem; color: #475569; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .table-actions span, .table-pagination span { background-color: #e2e8f0; padding: 0.25rem 0.75rem; border-radius: 9999px; }
    </style>
</head>
<body>
<div id="news-images-table"
     data-poll="30s"
     data-persist-filters-in-session="true"
     data-persist-sort-in-session="true"
     data-persist-search-in-session="true">
    <!-- data-poll=&quot;30s&quot; -->
    <!-- data-persist-filters-in-session=&quot;true&quot; -->
    <!-- data-persist-sort-in-session=&quot;true&quot; -->
    <!-- data-persist-search-in-session=&quot;true&quot; -->
    <h1>{{ __('admin.news_images_table.title') }}</h1>

    <div class="filters">
        <strong>{{ __('admin.news_images_table.active_filters') }}</strong>
        <span>{{ $activeFilters !== '' ? $activeFilters : __('admin.news_images_table.none') }}</span>
    </div>

    <table aria-describedby="news-images-table">
        <thead>
        <tr>
            <th>{{ __('admin.news_images_table.columns.preview') }}</th>
            <th>{{ __('admin.news_images_table.columns.news') }}</th>
            <th>{{ __('admin.news_images_table.columns.alt_text') }}</th>
            <th>{{ __('admin.news_images_table.columns.caption') }}</th>
            <th>{{ __('admin.news_images_table.columns.featured') }}</th>
            <th>{{ __('admin.news_images_table.columns.sort_order') }}</th>
            <th>{{ __('admin.news_images_table.columns.file_size') }}</th>
            <th>{{ __('admin.news_images_table.columns.mime_type') }}</th>
            <th>{{ __('admin.news_images_table.columns.dimensions') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($images as $image)
            <tr data-record-id="{{ $image->id }}">
                <td class="preview">
                    <img src="{{ $image->thumbnail_url }}" alt="{{ $image->alt_text ?? __('admin.news_images_table.preview_alt') }}">
                </td>
                <td>{{ __('admin.news_images_table.news_reference', ['id' => $image->news_id]) }}</td>
                <td>{{ $image->alt_text ?? __('admin.common.not_available') }}</td>
                <td>{{ $image->caption ?? __('admin.common.not_available') }}</td>
                <td>{{ $image->is_featured ? __('admin.common.yes') : __('admin.common.no') }}</td>
                <td>{{ $image->sort_order }}</td>
                <td>{{ $image->file_size_formatted }}</td>
                <td>{{ $image->mime_type ?? __('admin.common.not_available') }}</td>
                <td>
                    @if(is_array($image->dimensions) && isset($image->dimensions['width'], $image->dimensions['height']))
                        {{ $image->dimensions['width'] }}x{{ $image->dimensions['height'] }}
                    @else
                        {{ __('admin.common.not_available') }}
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">{{ __('admin.news_images_table.empty') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="table-actions">
        <strong>{{ __('admin.news_images_table.available_actions') }}</strong>
        <span>{{ __('admin.common.view') }}</span>
        <span>{{ __('admin.common.edit') }}</span>
        <span>{{ __('admin.common.duplicate') }}</span>
        <span>{{ __('admin.common.download') }}</span>
        <span>{{ __('admin.common.delete') }}</span>
    </div>

    <div class="table-pagination">
        <strong>{{ __('admin.news_images_table.per_page') }}</strong>
        <span>10</span>
        <span>25</span>
        <span>50</span>
        <span>100</span>
    </div>
</div>
</body>
</html>
