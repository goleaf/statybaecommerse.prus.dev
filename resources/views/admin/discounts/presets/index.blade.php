<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Discount Presets</title>
    <!-- Basic styling keeps the page readable without relying on external assets. -->
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background-color: #f9fafb; color: #1f2933; }
        h1 { margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { border: 1px solid #d2d6dc; padding: 0.75rem; text-align: left; }
        th { background-color: #e4e7eb; }
        tbody tr:nth-child(even) { background-color: #f1f5f9; }
        .status { margin-bottom: 1rem; padding: 0.75rem; background-color: #def7ec; border: 1px solid #31c48d; }
        form { background-color: #fff; padding: 1.5rem; border: 1px solid #d2d6dc; border-radius: 0.5rem; }
        label { display: block; font-weight: bold; margin-top: 1rem; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid #d2d6dc; border-radius: 0.25rem; }
        button { margin-top: 1.5rem; background-color: #2563eb; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 0.375rem; cursor: pointer; }
        button:hover { background-color: #1d4ed8; }
        .conditions-help { font-size: 0.85rem; color: #52606d; }
    </style>
</head>
<body>
    <h1>Discount Presets</h1>

    @if (session('status'))
        <div class="status">
            <!-- Flash messages provide quick feedback after submitting the form. -->
            {{ session('status') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Value</th>
                <th>Conditions</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($presets as $preset)
                <tr>
                    <td>{{ $preset['name'] }}</td>
                    <td>{{ $preset['description'] }}</td>
                    <td>{{ ucfirst($preset['type']) }}</td>
                    <td>{{ $preset['type'] === 'percentage' ? $preset['value'] . '%' : '€' . number_format($preset['value'], 2) }}</td>
                    <td>
                        @if (! empty($preset['conditions']))
                            <ul>
                                @foreach ($preset['conditions'] as $condition)
                                    <li>{{ $condition }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span>—</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($preset['created_at'])->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No discount presets available yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <form method="POST" action="{{ route('admin.discounts.presets.store') }}">
        @csrf
        <!-- Form allows administrators to add new preset definitions. -->
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
        @error('name')
            <div>{{ $message }}</div>
        @enderror

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
        @error('description')
            <div>{{ $message }}</div>
        @enderror

        <label for="type">Type</label>
        <select id="type" name="type" required>
            <option value="percentage" @selected(old('type', 'percentage') === 'percentage')>Percentage</option>
            <option value="fixed" @selected(old('type') === 'fixed')>Fixed Amount</option>
        </select>
        @error('type')
            <div>{{ $message }}</div>
        @enderror

        <label for="value">Value</label>
        <input id="value" name="value" type="number" min="0" step="0.01" value="{{ old('value', 0) }}" required>
        @error('value')
            <div>{{ $message }}</div>
        @enderror

        <label for="conditions">Conditions</label>
        <textarea id="conditions" name="conditions" rows="2" placeholder="Enter one condition per line">{{ old('conditions') }}</textarea>
        <p class="conditions-help">Example conditions: <code>applies_to:seasonal</code>, <code>customer_group:vip</code></p>
        @error('conditions')
            <div>{{ $message }}</div>
        @enderror

        <button type="submit">Save Preset</button>
    </form>
</body>
</html>
