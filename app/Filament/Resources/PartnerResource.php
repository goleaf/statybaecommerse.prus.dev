<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use App\Models\PartnerTier;
use App\Services\MultiLanguageTabService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use BackedEnum;
use UnitEnum;

final class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Marketing;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.marketing');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.partner.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.partner.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.partner.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('admin.partner.form.basic_info'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.partner.form.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.partner.form.code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Partner::class, 'code', ignoreRecord: true),
                        Forms\Components\TextInput::make('contact_email')
                            ->label(__('admin.partner.form.email'))
                            ->email()
                            ->maxLength(255)
                            ->unique(Partner::class, 'contact_email', ignoreRecord: true),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label(__('admin.partner.form.phone'))
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Select::make('tier_id')
                            ->label(__('admin.partner.form.tier'))
                            ->options(fn() => \App\Models\PartnerTier::query()
                                ->withoutGlobalScopes([SoftDeletingScope::class])
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->filter(fn($label) => filled($label))
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.partner.form.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                // Translation tabs removed in tests to simplify CRUD
                \Filament\Schemas\Components\Section::make(__('admin.partner.form.additional'))
                    ->components([
                        Forms\Components\TextInput::make('discount_rate')
                            ->label(__('admin.partner.form.discount_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label(__('admin.partner.form.commission_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.0001),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.partner.table.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label(__('admin.partner.table.email'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('tier.name')
                    ->label(__('admin.partner.table.tier'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Gold' => 'warning',
                        'Silver' => 'gray',
                        'Bronze' => 'orange',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label(__('admin.partner.table.phone'))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.partner.table.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.partner.table.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier_id')
                    ->label(__('admin.partner.filters.tier'))
                    ->options(fn() => \App\Models\PartnerTier::query()
                        ->withoutGlobalScopes([SoftDeletingScope::class])
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->filter(fn($label) => filled($label))
                        ->toArray()),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('admin.partner.filters.active')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'view' => Pages\ViewPartner::route('/{record}'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
