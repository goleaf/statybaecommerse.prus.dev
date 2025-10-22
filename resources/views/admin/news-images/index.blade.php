<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>News Images</title>
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
    <h1>News Images</h1>

    <div class="filters">
        <strong>Active Filters:</strong>
        <span>{{ $activeFilters !== '' ? $activeFilters : 'None' }}</span>
    </div>

    <table aria-describedby="news-images-table">
        <thead>
        <tr>
            <th>Preview</th>
            <th>News</th>
            <th>Alt Text</th>
            <th>Caption</th>
            <th>Featured</th>
            <th>Sort Order</th>
            <th>File Size</th>
            <th>MIME Type</th>
            <th>Dimensions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($images as $image)
            <tr data-record-id="{{ $image->id }}">
                <td class="preview">
                    <img src="{{ $image->thumbnail_url }}" alt="{{ $image->alt_text ?? 'Preview image' }}">
                </td>
                <td>{{ 'News #'.$image->news_id }}</td>
                <td>{{ $image->alt_text ?? '—' }}</td>
                <td>{{ $image->caption ?? '—' }}</td>
                <td>{{ $image->is_featured ? 'Yes' : 'No' }}</td>
                <td>{{ $image->sort_order }}</td>
                <td>{{ $image->file_size_formatted }}</td>
                <td>{{ $image->mime_type ?? '—' }}</td>
                <td>
                    @if(is_array($image->dimensions) && isset($image->dimensions['width'], $image->dimensions['height']))
                        {{ $image->dimensions['width'] }}x{{ $image->dimensions['height'] }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No news images found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="table-actions">
        <strong>Available Actions:</strong>
        <span>View</span>
        <span>Edit</span>
        <span>Duplicate</span>
        <span>Download</span>
        <span>Delete</span>
    </div>

    <div class="table-pagination">
        <strong>Per Page:</strong>
        <span>10</span>
        <span>25</span>
        <span>50</span>
        <span>100</span>
    </div>
</div>
</body>
</html>
