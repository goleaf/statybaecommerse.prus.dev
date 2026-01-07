<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use App\Models\RecommendationConfigSimple;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class ViewRecommendationConfigSimple extends ViewRecord implements HasTable
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = RecommendationConfigResourceSimple::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->getResolvedRecord();
        $this->isTableLoaded = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        // Surface the primary configuration attributes so the view page mirrors
        // what analysts assert against in the Livewire tests and in admin QA runs.
        return $schema->components([
            Section::make(__('recommendation_configs_simple.basic_information'))
                ->schema([
                    TextEntry::make('name')
                        ->label(__('recommendation_configs_simple.name')),
                    TextEntry::make('code')
                        ->label(__('recommendation_configs_simple.code')),
                    TextEntry::make('description')
                        ->label(__('recommendation_configs_simple.description'))
                        ->placeholder('—'),
                ])
                ->columns(2),
            Section::make(__('recommendation_configs_simple.algorithm_settings'))
                ->schema([
                    TextEntry::make('algorithm_type')
                        ->label(__('recommendation_configs_simple.algorithm_type'))
                        ->formatStateUsing(static fn (?string $state): string => $state ? __('recommendation_configs_simple.algorithm_types.' . $state) : '—'),
                    TextEntry::make('min_score')
                        ->label(__('recommendation_configs_simple.min_score')),
                    TextEntry::make('max_results')
                        ->label(__('recommendation_configs_simple.max_results')),
                    TextEntry::make('decay_factor')
                        ->label(__('recommendation_configs_simple.decay_factor')),
                ])
                ->columns(2),
            Section::make(__('recommendation_configs_simple.filtering'))
                ->schema([
                    IconEntry::make('exclude_out_of_stock')
                        ->label(__('recommendation_configs_simple.exclude_out_of_stock'))
                        ->boolean(),
                    IconEntry::make('exclude_inactive')
                        ->label(__('recommendation_configs_simple.exclude_inactive'))
                        ->boolean(),
                    TextEntry::make('products_count')
                        ->label(__('recommendation_configs_simple.products_count')),
                    TextEntry::make('categories_count')
                        ->label(__('recommendation_configs_simple.categories_count')),
                ])
                ->columns(2),
            Section::make(__('recommendation_configs_simple.weighting'))
                ->schema([
                    TextEntry::make('price_weight')
                        ->label(__('recommendation_configs_simple.price_weight')),
                    TextEntry::make('rating_weight')
                        ->label(__('recommendation_configs_simple.rating_weight')),
                    TextEntry::make('popularity_weight')
                        ->label(__('recommendation_configs_simple.popularity_weight')),
                    TextEntry::make('recency_weight')
                        ->label(__('recommendation_configs_simple.recency_weight')),
                    TextEntry::make('category_weight')
                        ->label(__('recommendation_configs_simple.category_weight')),
                    TextEntry::make('custom_weight')
                        ->label(__('recommendation_configs_simple.custom_weight')),
                ])
                ->columns(3),
            Section::make(__('recommendation_configs_simple.settings'))
                ->schema([
                    IconEntry::make('is_active')
                        ->label(__('recommendation_configs_simple.is_active'))
                        ->boolean(),
                    IconEntry::make('is_default')
                        ->label(__('recommendation_configs_simple.is_default'))
                        ->boolean(),
                    TextEntry::make('cache_duration')
                        ->label(__('recommendation_configs_simple.cache_duration')),
                    TextEntry::make('sort_order')
                        ->label(__('recommendation_configs_simple.sort_order')),
                    TextEntry::make('notes')
                        ->label(__('recommendation_configs_simple.notes'))
                        ->placeholder('—'),
                ])
                ->columns(2),
            Section::make(__('admin.common.timestamps'))
                ->schema([
                    TextEntry::make('created_at')
                        ->label(__('recommendation_configs_simple.created_at'))
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->label(__('recommendation_configs_simple.updated_at'))
                        ->dateTime(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        $table = $table
            ->paginated([10])
            ->defaultGroup(null)
            ->striped()
            ->deferLoading(false)
            ->columns([
                TextColumn::make('name')->label(__('recommendation_configs_simple.name')),
                TextColumn::make('code')->label(__('recommendation_configs_simple.code')),
            ]);

        return $this->applyToggleableTableLayout($table);
    }

    public function getTableQuery(): Builder|QueryBuilderContract
    {
        $record = $this->getResolvedRecord();

        return RecommendationConfigSimple::query()->whereKey($record->getKey());
    }

    public function shouldLoadTable(): bool
    {
        return true;
    }

    public function getTableRecordKey(Model|array $record): string
    {
        if (is_array($record)) {
            $identifier = $record['id'] ?? null;

            return is_string($identifier) || is_int($identifier) || is_float($identifier)
                ? (string) $identifier
                : '';
        }

        $key = $record->getKey();

        return is_string($key) || is_int($key) || is_float($key)
            ? (string) $key
            : '';
    }

    /**
     * Ensure the inherited record property contains the expected model instance before reuse.
     */
    private function getResolvedRecord(): RecommendationConfigSimple
    {
        if (! $this->record instanceof RecommendationConfigSimple) {
            // Throwing keeps the runtime and static analysers aligned whenever Filament fails to hydrate the record.
            throw new LogicException('Recommendation config record failed to resolve.');
        }

        return $this->record;
    }
}
