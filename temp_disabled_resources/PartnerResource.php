<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use App\Models\PartnerTier;
use App\Services\MultiLanguageTabService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use BackedEnum;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\Section::make(__('admin.partner.form.basic_info'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.partner.form.name'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label(__('admin.partner.form.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(Partner::class, 'email', ignoreRecord: true),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label(__('admin.partner.form.phone'))
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('website')
                            ->label(__('admin.partner.form.website'))
                            ->url()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('partner_tier_id')
                            ->label(__('admin.partner.form.tier'))
                            ->relationship('partnerTier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.partner.form.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make(__('admin.partner.form.address'))
                    ->components([
                        Forms\Components\TextInput::make('address')
                            ->label(__('admin.partner.form.address_line'))
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('city')
                            ->label(__('admin.partner.form.city'))
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('state')
                            ->label(__('admin.partner.form.state'))
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('admin.partner.form.postal_code'))
                            ->maxLength(20),
                        
                        Forms\Components\Select::make('country_id')
                            ->label(__('admin.partner.form.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                
                // Multilanguage Tabs for Partner Content
                Tabs::make('partner_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'partner_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.description'),
                                    'maxLength' => 1000,
                                    'rows' => 3,
                                    'placeholder' => __('translations.partner_description_help'),
                                ],
                                'notes' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.notes'),
                                    'maxLength' => 1000,
                                    'rows' => 3,
                                    'placeholder' => __('translations.partner_notes_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('partner_tab')
                    ->contained(false),
                
                Forms\Components\Section::make(__('admin.partner.form.additional'))
                    ->components([
                        // Non-translatable additional fields can go here
                    ])
                    ->columns(1),
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
                
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.partner.table.email'))
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('partnerTier.name')
                    ->label(__('admin.partner.table.tier'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Gold' => 'warning',
                        'Silver' => 'gray',
                        'Bronze' => 'orange',
                        default => 'primary',
                    }),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('admin.partner.table.phone'))
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->label(__('admin.partner.table.city'))
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('admin.partner.table.country'))
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.partner.table.active'))
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.partner.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('partner_tier_id')
                    ->label(__('admin.partner.filters.tier'))
                    ->relationship('partnerTier', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('admin.partner.filters.active')),
                
                Tables\Filters\SelectFilter::make('country')
                    ->label(__('admin.partner.filters.country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'view' => Pages\ViewPartner::route('/{record}'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}