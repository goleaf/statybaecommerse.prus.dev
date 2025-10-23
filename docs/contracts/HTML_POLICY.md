# Rich Text HTML Policy

The storefront only renders HTML that passes through the centralized `App\Support\Html\HtmlSanitizer`. The policy is deliberately conservative so that common formatting survives while embedded scripts, trackers, and layout-breaking markup are stripped.

## Allowed elements

The following tags are preserved when present in product descriptions or static pages:

- Inline formatting: `<a>`, `<abbr>`, `<b>`, `<em>`, `<i>`, `<s>`, `<span>`, `<strong>`, `<sub>`, `<sup>`, `<u>`
- Paragraph and block content: `<p>`, `<div>`, `<blockquote>`, `<pre>`, `<code>`, `<br>`, `<hr>`
- Headings: `<h2>`, `<h3>`, `<h4>`, `<h5>`, `<h6>`
- Lists: `<ul>`, `<ol>`, `<li>`
- Tables: `<table>`, `<thead>`, `<tbody>`, `<tfoot>`, `<tr>`, `<th>`, `<td>`, `<caption>`, `<figcaption>`, `<figure>`

Everything else is either unwrapped (so the textual content remains) or removed entirely for high-risk tags such as `<script>`, `<style>`, `<iframe>`, `<object>`, `<embed>`, `<form>`, and `<input>`.

## Allowed attributes

Only a short allow-list of attributes is honoured:

- Global: `style`
- Links: `href`, `title`, `target` (restricted to `_blank`), and `rel` (the sanitizer automatically adds `noopener noreferrer` for blank-target links)
- Table cells: `colspan`, `rowspan` (plus `scope` on header cells)
- Ordered lists: `start`, `reversed`, `type`

All `on*` event handlers, dataset attributes, and any other attributes are removed.

## Allowed CSS properties

Inline styles are trimmed to the following properties:

- `color`
- `background-color`
- `font-style`
- `font-weight`
- `text-align`
- `text-decoration`

Values are canonicalised (e.g. lowercase hex colours, restricted keywords, and safe RGB/A notation). Unsupported properties or values are discarded entirely.

## URL schemes

Links are limited to `http`, `https`, `mailto`, and `tel` schemes. Hash (`#anchor`) and root-relative (`/path`) links are also accepted. Any `javascript:` or `data:` URL is removed before persisting.

## Rendering

Blade templates should avoid `{!! !!}` directly. Use the `x-sanitized-html` component so only pre-sanitized fields are rendered as HTML. This keeps the default behaviour (`{{ }}`) safely escaped everywhere else.

## Maintenance

Run `php artisan maintenance:sanitize-html` to re-process legacy content. Use `--dry-run` to preview how many records would change and `--chunk` to tune batch size for large datasets.
