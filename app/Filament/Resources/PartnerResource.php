<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

final class PartnerResource extends Resource
{
    use HasNav;

    protected static ?string $model = Partner::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-user-group';

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form
            ->schema([
                Section::make(__('admin.partners.sections.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.partners.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.partners.code'))
                            ->maxLength(255)
                            ->unique(Partner::class, 'code', ignoreRecord: true),
                        Forms\Components\Select::make('tier_id')
                            ->label(__('admin.partners.tier'))
                            ->relationship('tier', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.partners.is_enabled'))
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make(__('admin.partners.sections.contact_information'))
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->label(__('admin.partners.contact_email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label(__('admin.partners.contact_phone'))
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make(__('admin.partners.sections.financial_settings'))
                    ->schema([
                        Forms\Components\TextInput::make('discount_rate')
                            ->label(__('admin.partners.discount_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label(__('admin.partners.commission_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                    ])
                    ->columns(2),
                Section::make(__('admin.partners.sections.media'))
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label(__('admin.partners.logo'))
                            ->image()
                            ->directory('partner-logos')
                            ->visibility('private'),
                        Forms\Components\FileUpload::make('banner')
                            ->label(__('admin.partners.banner'))
                            ->image()
                            ->directory('partner-banners')
                            ->visibility('private'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.partners.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.partners.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tier.name')
                    ->label(__('admin.partners.tier'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.partners.is_enabled'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label(__('admin.partners.discount_rate'))
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(__('admin.partners.commission_rate'))
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier_id')
                    ->label(__('admin.partners.tier'))
                    ->relationship('tier', 'name'),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('admin.partners.is_enabled')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index'  => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'view'   => Pages\ViewPartner::route('/{record}'), // Provide a dedicated read-only partner detail page.
            'edit'   => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
