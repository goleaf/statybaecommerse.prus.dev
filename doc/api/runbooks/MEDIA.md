# Media Processing Runbook

This runbook documents how product and marketing media is processed, stored, and maintained across the storefront.

## Storage quotas

- **Maximum upload size**: Controlled via `config/media.php` (`media.max_upload_size`). The default limit is **10&nbsp;MB** per asset.
- **Allowed formats**: JPEG, PNG, WebP, and AVIF. Other formats are rejected before storage to prevent executable payloads.
- **Disk usage monitoring**: Use `php artisan storage:link` in non-production environments to ensure the public disk is available, then run `du -sh storage/app/public` to review current usage.
- **Override per environment**: Set the `MEDIA_MAX_UPLOAD` environment variable to raise or lower limits when syncing large catalogs.

## Variant generation

1. Uploads flow through `App\Services\MediaService::upload()`, which stores the original file and queues `App\Jobs\GenerateMediaVariantsJob`.
2. The job creates responsive WebP variants using the dimensions defined in `config/media.php` (`thumb`, `medium`, `large`).
3. Metadata (dimensions, byte size, MIME type, URLs) is recorded on the Spatie media model for later rendering with the `media_img()` helper.
4. When variant generation fails (missing GD/Imagick, disk issues), warnings are logged; the original file remains available so retries can be scheduled.

## Cleaning stale media

- **Remove orphaned variants**: Call the service from Tinker: `app(App\Services\MediaService::class)->deleteMedia($media);`. This deletes the original and every generated variant safely.
- **Automated cleanup**: For batch removal, iterate over IDs (e.g. `Media::whereDoesntHaveMorph('model', '*')->each(fn ($media) => app(MediaService::class)->deleteMedia($media));`).
- **Disk hygiene**: Schedule `php artisan storage:link` verification and optionally a nightly `php -r "opcache_reset();"` if running behind long-lived PHP processes.

## Re-generating variants

- **Single media item**: `php artisan tinker --execute="app(App\\Services\\MediaService::class)->processVariants(Spatie\\MediaLibrary\\MediaCollections\\Models\\Media::findOrFail($id));"`
- **Bulk re-generation**: Loop through models and dispatch jobs: `Media::chunkById(100, fn ($chunk) => $chunk->each(fn ($media) => dispatch(new App\Jobs\GenerateMediaVariantsJob($media->id, config('media.variants')))));`
- **After changing variant sizes**: Update `config/media.php`, clear the cache (`php artisan config:clear`), then run the bulk command above to refresh all stored variants.

## Operational tips

- Ensure the queue worker is running (`php artisan queue:work`) so uploads generate variants promptly.
- Use the `media_img()` helper in Blade views to output `<img>` tags with `srcset`, `sizes`, lazy loading, and locale-aware direction.
- When seeding demo data, prefer `.webp` fixtures to minimize conversion work and exercise the helper’s `srcset` output.
