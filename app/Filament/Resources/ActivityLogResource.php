<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Activitylog\Models\Activity;
use BackedEnum;
use UnitEnum;

final class ActivityLogResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'System';
    }

    protected static ?string $model = Activity::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'Activity Log';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-document-text';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label('Log Name')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                TextColumn::make('subject_type')
                    ->label('Subject Type')
                    ->sortable(),
                TextColumn::make('event')
                    ->label('Event')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
