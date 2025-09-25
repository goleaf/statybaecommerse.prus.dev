<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use App\Models\RecommendationConfigSimple;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ViewRecommendationConfigSimple extends ViewRecord implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = RecommendationConfigResourceSimple::class;

    public function mount($record): void
    {
        parent::mount($record);
        $this->isTableLoaded = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([10])
            ->defaultGroup(null)
            ->striped()
            ->deferLoading(false)
            ->columns([
                TextColumn::make('name')->label(__('recommendation_configs_simple.name')),
                TextColumn::make('code')->label(__('recommendation_configs_simple.code')),
            ]);
    }

    public function getTableQuery(): Builder|QueryBuilderContract
    {
        return RecommendationConfigSimple::query()->whereKey($this->record->getKey());
    }

    public function shouldLoadTable(): bool
    {
        return true;
    }

    public function getTableRecordKey(Model|array $record): string
    {
        return is_array($record) ? (string) ($record['id'] ?? '') : (string) $record->getKey();
    }
}
