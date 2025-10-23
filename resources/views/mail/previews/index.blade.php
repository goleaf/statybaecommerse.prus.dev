<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $selectedLocale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Mail previews') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            margin: 0;
            padding: 2.5rem 1.5rem;
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.875rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        p {
            color: #cbd5f5;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 2rem 0 0;
            display: grid;
            gap: 1rem;
        }

        a.preview-link {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: inherit;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: transform 0.15s ease, background 0.15s ease, border 0.15s ease;
        }

        a.preview-link:hover,
        a.preview-link:focus-visible {
            transform: translateY(-2px);
            background: rgba(148, 163, 184, 0.18);
            border-color: rgba(255, 255, 255, 0.35);
        }

        .locale-selector {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        select {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            color: inherit;
        }

        small {
            color: #94a3b8;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>{{ __('Transactional mail previews') }}</h1>
    <p>{{ __('Quickly inspect the rendered HTML for key transactional emails. Open any preview in a new tab to review the final markup sent to users.') }}</p>

    <form class="locale-selector" method="get">
        <label for="locale">{{ __('Locale') }}</label>
        <select id="locale" name="locale" onchange="this.form.submit()">
            @foreach ($availableLocales as $locale)
                <option value="{{ $locale }}" @selected($locale === $selectedLocale)>{{ strtoupper($locale) }}</option>
            @endforeach
        </select>
    </form>

    <ul>
        @foreach ($previews as $preview)
            <li>
                <a class="preview-link" href="{{ route('mail-previews.show', ['mail' => $preview['slug'], 'locale' => $selectedLocale]) }}" target="_blank" rel="noreferrer">
                    <span>{{ $preview['label'] }}</span>
                    <small>{{ $preview['slug'] }}</small>
                </a>
            </li>
        @endforeach
    </ul>
</div>
</body>
</html>
