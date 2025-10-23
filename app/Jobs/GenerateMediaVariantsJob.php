<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\MediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

final class GenerateMediaVariantsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 3;

    /**
     * Define retry backoff windows (in seconds).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    /**
     * @param  array<string, array<string, int>>  $variants
     */
    public function __construct(
        private readonly int $mediaId,
        private readonly array $variants
    ) {}

    public function handle(MediaService $mediaService): void
    {
        $media = Media::find($this->mediaId);

        if (! $media) {
            return;
        }

        try {
            $mediaService->processVariants($media, $this->variants);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function mediaId(): int
    {
        return $this->mediaId;
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function variants(): array
    {
        return $this->variants;
    }
}
