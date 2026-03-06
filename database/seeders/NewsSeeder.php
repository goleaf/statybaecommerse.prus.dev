<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ModerationState;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\Translations\NewsTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class NewsSeeder extends BaseSeeder
{
    private const TOTAL_ITEMS = 24;

    private const FRONTEND_PUBLISHED_ITEMS = 16;

    public function run(): void
    {
        $newsColumns = $this->tableColumns('news');
        $newsImageColumns = $this->tableColumns('news_images');
        $supportsSoftDeletes = array_key_exists('deleted_at', $newsColumns);
        $locales = $this->resolveLocales();
        $adminUserId = Schema::hasTable('admin_users')
            ? DB::table('admin_users')->value('id')
            : null;

        for ($index = 1; $index <= self::TOTAL_ITEMS; $index++) {
            $state = $this->resolveModerationState($index);
            $publishedAt = $state === ModerationState::Published->value
                ? now()->subDays($index)
                : null;
            $submittedForReviewAt = $state === ModerationState::Draft->value
                ? null
                : now()->subDays($index + 2);
            $approvedAt = $state === ModerationState::Published->value
                ? now()->subDays($index + 1)
                : null;
            $seedEmail = $this->seedEmail($index);

            $query = News::withoutGlobalScopes();

            if ($supportsSoftDeletes) {
                $query = $query->withTrashed();
            }

            $news = $query->firstOrNew(['author_email' => $seedEmail]);

            if ($supportsSoftDeletes && $news->exists && $news->trashed()) {
                $news->restore();
            }

            $newsData = [
                'is_visible'              => $state !== ModerationState::Draft->value,
                'is_featured'             => $state === ModerationState::Published->value && $index <= 4,
                'is_breaking'             => $state === ModerationState::Published->value && $index <= 2,
                'moderation_state'        => $state,
                'submitted_for_review_at' => $submittedForReviewAt,
                'approved_at'             => $approvedAt,
                'approved_by_id'          => null,
                'created_by_id'           => $adminUserId,
                'updated_by_id'           => $adminUserId,
                'published_at'            => $publishedAt,
                'author_name'             => 'Lorem Ipsum Editorial Team',
                'author_email'            => $seedEmail,
                'view_count'              => max(0, (self::TOTAL_ITEMS - $index + 1) * 37),
            ];
            $news->fill(array_intersect_key($newsData, $newsColumns));
            $news->save();

            $this->syncTranslations($news, $index, $locales);

            if ($newsImageColumns !== []) {
                $imageCount = $state === ModerationState::Published->value ? 2 : 1;
                $this->syncImages($news, $index, $imageCount, $newsImageColumns);
            }
        }
    }

    private function seedEmail(int $index): string
    {
        return sprintf('info@egisstatyba.lt', $index);
    }

    private function resolveModerationState(int $index): string
    {
        if ($index <= self::FRONTEND_PUBLISHED_ITEMS) {
            return ModerationState::Published->value;
        }

        if ($index <= self::FRONTEND_PUBLISHED_ITEMS + 4) {
            return ModerationState::Review->value;
        }

        return ModerationState::Draft->value;
    }

    /**
     * @return array<int, string>
     */
    private function resolveLocales(): array
    {
        $supportedLocales = config('app.supported_locales', 'lt,en,ru,de');
        $locales = [];

        if (is_array($supportedLocales)) {
            $locales = $supportedLocales;
        } else {
            $locales = explode(',', (string) $supportedLocales);
        }

        $locales = collect($locales)
            ->map(static fn (mixed $locale): string => strtolower(trim((string) $locale)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($locales === []) {
            return ['lt', 'en', 'ru', 'de'];
        }

        return $locales;
    }

    /**
     * @param array<int, string> $locales
     */
    private function syncTranslations(News $news, int $index, array $locales): void
    {
        foreach ($locales as $locale) {
            $title = $this->localizedTitle($locale, $index);
            $slugBase = Str::slug($title);

            if ($slugBase === '') {
                $slugBase = 'news-item';
            }

            $slug = sprintf('%s-%d-%s', $slugBase, $index, $locale);
            $summary = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
            $content = sprintf(
                '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Seed item %d.</p>',
                $index
            );

            NewsTranslation::query()->updateOrCreate(
                [
                    'news_id' => $news->id,
                    'locale'  => $locale,
                ],
                [
                    'title'           => $title,
                    'slug'            => $slug,
                    'summary'         => $summary,
                    'content'         => $content,
                    'seo_title'       => $title,
                    'seo_description' => $summary,
                ]
            );
        }
    }

    private function localizedTitle(string $locale, int $index): string
    {
        return match ($locale) {
            'lt'    => "Lorem ipsum naujiena {$index}",
            'ru'    => "Lorem ipsum новость {$index}",
            'de'    => "Lorem ipsum nachricht {$index}",
            default => "Lorem ipsum news {$index}",
        };
    }

    /**
     * @param array<string, bool> $newsImageColumns
     */
    private function syncImages(News $news, int $index, int $imageCount, array $newsImageColumns): void
    {
        for ($imageIndex = 1; $imageIndex <= $imageCount; $imageIndex++) {
            $seedPath = sprintf(
                'https://picsum.photos/seed/egistatyba-news-%d-%d/1600/900',
                $index,
                $imageIndex
            );

            $newsImageData = [
                'file_path'   => $seedPath,
                'alt_text'    => sprintf('Lorem ipsum image %d-%d', $index, $imageIndex),
                'caption'     => sprintf('Lorem ipsum dolor sit amet image %d-%d.', $index, $imageIndex),
                'is_featured' => $imageIndex === 1,
                'file_size'   => 350000 + ($imageIndex * 5000),
                'mime_type'   => 'image/jpeg',
                'dimensions'  => [
                    'width'  => 1600,
                    'height' => 900,
                ],
            ];

            NewsImage::withoutGlobalScopes()->updateOrCreate(
                [
                    'news_id'    => $news->id,
                    'sort_order' => $imageIndex,
                ],
                array_intersect_key($newsImageData, $newsImageColumns)
            );
        }
    }

    /**
     * @return array<string, bool>
     */
    private function tableColumns(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return collect(Schema::getColumnListing($table))
            ->mapWithKeys(static fn (string $column): array => [$column => true])
            ->all();
    }
}
