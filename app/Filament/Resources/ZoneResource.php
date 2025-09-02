<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('admin.zone.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.zone.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.zone.form.basic_info'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.zone.form.name'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.zone.form.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('enabled')
                            ->label(__('admin.zone.form.enabled'))
                            ->default(true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make(__('admin.zone.form.countries'))
                    ->schema([
                        Forms\Components\CheckboxList::make('countries')
                            ->label(__('admin.zone.form.countries_list'))
                            ->relationship('countries', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3)
                            ->helperText(__('admin.zone.form.countries_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.zone.table.name'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.zone.table.description'))
                    ->limit(50)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('countries_count')
                    ->label(__('admin.zone.table.countries_count'))
                    ->counts('countries')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('enabled')
                    ->label(__('admin.zone.table.enabled'))
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.zone.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.zone.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label(__('admin.zone.filters.enabled')),
                
                Tables\Filters\Filter::make('has_countries')
                    ->label(__('admin.zone.filters.has_countries'))
                    ->query(fn (Builder $query): Builder => $query->has('countries')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'view' => Pages\ViewZone::route('/{record}'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
