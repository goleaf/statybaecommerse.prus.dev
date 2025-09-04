<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use BackedEnum;
use UnitEnum;

final class SEOAnalytics extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 2;

    public ?string $seoEntityType = 'products';
    public array $seoIssues = [];

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.seo_analytics');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getSEOQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.table.slug'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('seo_title')
                    ->label(__('admin.table.seo_title'))
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->seo_title),

                Tables\Columns\TextColumn::make('seo_title_length')
                    ->label(__('admin.table.title_length'))
                    ->getStateUsing(fn ($record): int => strlen($record->seo_title ?? ''))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 30 => 'warning',
                        $state > 60 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('seo_description_length')
                    ->label(__('admin.table.desc_length'))
                    ->getStateUsing(fn ($record): int => strlen($record->seo_description ?? ''))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 120 => 'warning',
                        $state > 160 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\IconColumn::make('has_meta_keywords')
                    ->label(__('admin.table.keywords'))
                    ->getStateUsing(fn ($record): bool => !empty($record->meta_keywords))
                    ->boolean(),

                Tables\Columns\TextColumn::make('seo_score')
                    ->label(__('admin.table.seo_score'))
                    ->getStateUsing(fn ($record): int => $this->calculateSEOScore($record))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('seo_audit')
                    ->label(__('admin.actions.seo_audit'))
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->action(function (): void {
                        $this->performSEOAudit();
                    }),

                Tables\Actions\Action::make('generate_sitemaps')
                    ->label(__('admin.actions.generate_sitemaps'))
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->action(function (): void {
                        $this->generateSitemaps();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('optimize_seo')
                    ->label(__('admin.actions.optimize_seo'))
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->action(function ($record): void {
                        $this->optimizeSEO($record);
                    }),

                Tables\Actions\Action::make('preview_seo')
                    ->label(__('admin.actions.preview_seo'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn ($record) => view('filament.modals.seo-preview', compact('record')))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('admin.actions.close')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('seo_score_range')
                    ->label(__('admin.filters.seo_score'))
                    ->options([
                        'excellent' => __('admin.seo_scores.excellent') . ' (90-100)',
                        'good' => __('admin.seo_scores.good') . ' (70-89)',
                        'fair' => __('admin.seo_scores.fair') . ' (50-69)',
                        'poor' => __('admin.seo_scores.poor') . ' (0-49)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) return $query;
                        
                        return match ($data['value']) {
                            'excellent' => $query->whereRaw('LENGTH(COALESCE(seo_title, "")) BETWEEN 30 AND 60')
                                               ->whereRaw('LENGTH(COALESCE(seo_description, "")) BETWEEN 120 AND 160'),
                            'good' => $query->whereNotNull('seo_title')->whereNotNull('seo_description'),
                            'fair' => $query->where(function ($q) {
                                $q->whereNull('seo_title')->orWhereNull('seo_description');
                            }),
                            'poor' => $query->whereNull('seo_title')->whereNull('seo_description'),
                            default => $query,
                        };
                    }),

                Tables\Filters\TernaryFilter::make('missing_seo_title')
                    ->label(__('admin.filters.missing_seo_title'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('seo_title'),
                        false: fn (Builder $query) => $query->whereNotNull('seo_title'),
                    ),

                Tables\Filters\TernaryFilter::make('missing_seo_description')
                    ->label(__('admin.filters.missing_seo_description'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('seo_description'),
                        false: fn (Builder $query) => $query->whereNotNull('seo_description'),
                    ),
            ]);
    }

    protected function getSEOQuery(): Builder
    {
        return match ($this->seoEntityType) {
            'products' => Product::query(),
            'categories' => Category::query(),
            'brands' => Brand::query(),
            default => Product::query(),
        };
    }

    private function calculateSEOScore($record): int
    {
        $score = 0;

        // SEO Title (30 points)
        if (!empty($record->seo_title)) {
            $titleLength = strlen($record->seo_title);
            if ($titleLength >= 30 && $titleLength <= 60) {
                $score += 30;
            } elseif ($titleLength > 0) {
                $score += 15;
            }
        }

        // SEO Description (30 points)
        if (!empty($record->seo_description)) {
            $descLength = strlen($record->seo_description);
            if ($descLength >= 120 && $descLength <= 160) {
                $score += 30;
            } elseif ($descLength > 0) {
                $score += 15;
            }
        }

        // Meta Keywords (20 points)
        if (!empty($record->meta_keywords)) {
            $score += 20;
        }

        // Slug Quality (10 points)
        if (!empty($record->slug) && !str_contains($record->slug, '_') && strlen($record->slug) > 3) {
            $score += 10;
        }

        // Content Quality (10 points)
        if (!empty($record->description) && strlen($record->description) > 100) {
            $score += 10;
        }

        return $score;
    }

    private function performSEOAudit(): void
    {
        $issues = [];
        
        // Check for missing SEO titles
        $missingTitles = $this->getSEOQuery()->whereNull('seo_title')->count();
        if ($missingTitles > 0) {
            $issues[] = __('admin.seo_issues.missing_titles', ['count' => $missingTitles]);
        }

        // Check for missing SEO descriptions
        $missingDescriptions = $this->getSEOQuery()->whereNull('seo_description')->count();
        if ($missingDescriptions > 0) {
            $issues[] = __('admin.seo_issues.missing_descriptions', ['count' => $missingDescriptions]);
        }

        // Check for duplicate titles
        $duplicateTitles = $this->getSEOQuery()
            ->whereNotNull('seo_title')
            ->groupBy('seo_title')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        if ($duplicateTitles > 0) {
            $issues[] = __('admin.seo_issues.duplicate_titles', ['count' => $duplicateTitles]);
        }

        $this->seoIssues = $issues;

        Notification::make()
            ->title(__('admin.notifications.seo_audit_completed'))
            ->body(__('admin.notifications.found_issues', ['count' => count($issues)]))
            ->info()
            ->send();
    }

    private function generateSitemaps(): void
    {
        // Generate XML sitemaps for all locales
        $locales = ['lt', 'en', 'de'];
        $generated = [];

        foreach ($locales as $locale) {
            $sitemap = $this->buildSitemapForLocale($locale);
            $filename = "sitemap-{$locale}.xml";
            \Storage::disk('public')->put($filename, $sitemap);
            $generated[] = $filename;
        }

        Notification::make()
            ->title(__('admin.notifications.sitemaps_generated'))
            ->body(__('admin.notifications.generated_files', ['files' => implode(', ', $generated)]))
            ->success()
            ->send();
    }

    private function buildSitemapForLocale(string $locale): string
    {
        $urls = [];
        
        // Add products
        Product::where('is_visible', true)->chunk(100, function ($products) use (&$urls, $locale) {
            foreach ($products as $product) {
                $urls[] = [
                    'loc' => url("/{$locale}/products/{$product->slug}"),
                    'lastmod' => $product->updated_at->format('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        });

        // Add categories
        Category::where('is_visible', true)->chunk(100, function ($categories) use (&$urls, $locale) {
            foreach ($categories as $category) {
                $urls[] = [
                    'loc' => url("/{$locale}/categories/{$category->slug}"),
                    'lastmod' => $category->updated_at->format('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        });

        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    private function optimizeSEO($record): void
    {
        $updates = [];

        // Auto-generate SEO title if missing
        if (empty($record->seo_title)) {
            $updates['seo_title'] = substr($record->name, 0, 60);
        }

        // Auto-generate SEO description if missing
        if (empty($record->seo_description)) {
            $description = $record->description ?? $record->name;
            $updates['seo_description'] = substr(strip_tags($description), 0, 160);
        }

        // Generate meta keywords if missing
        if (empty($record->meta_keywords)) {
            $keywords = [$record->name];
            if ($record instanceof Product && $record->brand) {
                $keywords[] = $record->brand->name;
            }
            if ($record instanceof Product && $record->categories->isNotEmpty()) {
                $keywords = array_merge($keywords, $record->categories->pluck('name')->toArray());
            }
            $updates['meta_keywords'] = implode(', ', array_slice($keywords, 0, 10));
        }

        if (!empty($updates)) {
            $record->update($updates);

            Notification::make()
                ->title(__('admin.notifications.seo_optimized'))
                ->body(__('admin.notifications.seo_fields_updated', ['name' => $record->name]))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('admin.notifications.seo_already_optimized'))
                ->info()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_seo_optimize')
                ->label(__('admin.actions.bulk_seo_optimize'))
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription(__('admin.modals.bulk_seo_optimize_description'))
                ->action(function (): void {
                    $query = $this->getSEOQuery();
                    $optimized = 0;

                    $query->chunk(50, function ($records) use (&$optimized) {
                        foreach ($records as $record) {
                            $updates = [];

                            if (empty($record->seo_title)) {
                                $updates['seo_title'] = substr($record->name, 0, 60);
                            }

                            if (empty($record->seo_description)) {
                                $description = $record->description ?? $record->name;
                                $updates['seo_description'] = substr(strip_tags($description), 0, 160);
                            }

                            if (!empty($updates)) {
                                $record->update($updates);
                                $optimized++;
                            }
                        }
                    });

                    Notification::make()
                        ->title(__('admin.notifications.bulk_seo_completed'))
                        ->body(__('admin.notifications.optimized_records', ['count' => $optimized]))
                        ->success()
                        ->send();
                }),

            Action::make('check_broken_links')
                ->label(__('admin.actions.check_broken_links'))
                ->icon('heroicon-o-link')
                ->color('info')
                ->action(function (): void {
                    $this->checkBrokenLinks();
                }),
        ];
    }

    private function checkBrokenLinks(): void
    {
        $brokenLinks = [];
        $baseUrl = config('app.url');

        // Check product URLs
        Product::where('is_visible', true)->chunk(20, function ($products) use (&$brokenLinks, $baseUrl) {
            foreach ($products as $product) {
                $url = "{$baseUrl}/products/{$product->slug}";
                try {
                    $response = Http::timeout(5)->get($url);
                    if ($response->failed()) {
                        $brokenLinks[] = $url;
                    }
                } catch (\Exception $e) {
                    $brokenLinks[] = $url . ' (Error: ' . $e->getMessage() . ')';
                }
            }
        });

        Notification::make()
            ->title(__('admin.notifications.link_check_completed'))
            ->body(__('admin.notifications.broken_links_found', ['count' => count($brokenLinks)]))
            ->info()
            ->send();
    }
}
