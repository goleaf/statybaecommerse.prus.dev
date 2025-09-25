<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use App\Models\RecommendationConfig;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ViewRecommendationConfig extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = RecommendationConfigResource::class;

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
                TextColumn::make('name'),
            ]);
    }

    public function getTableQuery(): Builder|QueryBuilderContract
    {
        return RecommendationConfig::query()->whereKey($this->record->getKey());
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
