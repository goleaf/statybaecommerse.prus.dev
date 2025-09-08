<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Models\DiscountCode;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Discount Codes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('Discount Code Information'))
                    ->components([
                        Forms\Components\TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(DiscountCode::class, 'code', ignoreRecord: true)
                            ->suffixAction(
                                Forms\Components\Action::make('generate')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (Forms\Set $set) {
                                        $set('code', strtoupper(\Illuminate\Support\Str::random(8)));
                                    })
                            ),
                        Forms\Components\Select::make('discount_id')
                            ->label(__('Discount'))
                            ->relationship('discount', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        // Multilanguage description will be in tabs below
                        Forms\Components\Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'active' => __('Active'),
                                'inactive' => __('Inactive'),
                                'expired' => __('Expired'),
                                'used_up' => __('Used Up'),
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),

                // Multilanguage Tabs for Discount Code Content
                Tabs::make('discount_code_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'discount_code_information' => [
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.description'),
                                    'maxLength' => 500,
                                    'rows' => 3,
                                    'placeholder' => __('translations.discount_code_description_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('discount_code_tab')
                    ->contained(false),

                Forms\Components\Section::make(__('Usage Limits'))
                    ->components([
                        Forms\Components\TextInput::make('max_uses')
                            ->label(__('Maximum Uses'))
                            ->numeric()
                            ->minValue(1)
                            ->helperText(__('Leave empty for unlimited uses')),
                        Forms\Components\TextInput::make('max_uses_per_customer')
                            ->label(__('Max Uses Per Customer'))
                            ->numeric()
                            ->minValue(1)
                            ->helperText(__('Leave empty for unlimited uses per customer')),
                        Forms\Components\TextInput::make('current_uses')
                            ->label(__('Current Uses'))
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\TextInput::make('minimum_order_amount')
                            ->label(__('Minimum Order Amount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText(__('Minimum order amount required to use this code')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('Validity Period'))
                    ->components([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label(__('Valid From'))
                            ->native(false)
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label(__('Valid Until'))
                            ->native(false)
                            ->after('valid_from'),
                        Forms\Components\Toggle::make('is_unlimited_validity')
                            ->label(__('Unlimited Validity'))
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(fn($state, Forms\Set $set) => 
                                $state ? $set('valid_until', null) : null),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('Targeting'))
                    ->components([
                        Forms\Components\Select::make('customer_groups')
                            ->label(__('Customer Groups'))
                            ->relationship('customerGroups', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText(__('Leave empty to allow all customer groups')),
                        Forms\Components\Select::make('zones')
                            ->label(__('Zones'))
                            ->relationship('zones', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText(__('Leave empty to allow all zones')),
                        Forms\Components\TagsInput::make('allowed_emails')
                            ->label(__('Allowed Emails'))
                            ->helperText(__('Specific email addresses that can use this code')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('Settings'))
                    ->components([
                        Forms\Components\Toggle::make('is_single_use')
                            ->label(__('Single Use Only'))
                            ->default(false)
                            ->helperText(__('Code becomes invalid after first use')),
                        Forms\Components\Toggle::make('is_public')
                            ->label(__('Public Code'))
                            ->default(false)
                            ->helperText(__('Can be shared publicly')),
                        Forms\Components\Toggle::make('track_usage')
                            ->label(__('Track Usage'))
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('discount.name')
                    ->label(__('Discount'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'expired' => 'danger',
                        'used_up' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('current_uses')
                    ->label(__('Uses'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => 
                        $record->max_uses ? "{$state}/{$record->max_uses}" : (string)$state
                    ),
                Tables\Columns\TextColumn::make('minimum_order_amount')
                    ->label(__('Min. Order'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('Valid From'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('Valid Until'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('Never'))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_single_use')
                    ->label(__('Single Use'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => __('Active'),
                        'inactive' => __('Inactive'),
                        'expired' => __('Expired'),
                        'used_up' => __('Used Up'),
                    ]),
                Tables\Filters\SelectFilter::make('discount_id')
                    ->relationship('discount', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('single_use')
                    ->query(fn(Builder $query): Builder => $query->where('is_single_use', true)),
                Tables\Filters\Filter::make('public')
                    ->query(fn(Builder $query): Builder => $query->where('is_public', true)),
                Tables\Filters\Filter::make('expires_soon')
                    ->query(fn(Builder $query): Builder => 
                        $query->whereBetween('valid_until', [now(), now()->addDays(7)])
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (DiscountCode $record) {
                        $newCode = $record->replicate();
                        $newCode->code = $record->code . '-COPY-' . strtoupper(\Illuminate\Support\Str::random(4));
                        $newCode->current_uses = 0;
                        $newCode->save();
                        
                        return redirect()->to(static::getUrl('edit', ['record' => $newCode]));
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('Activate'))
                        ->icon('heroicon-o-check')
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['status' => 'active'])))
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('Deactivate'))
                        ->icon('heroicon-o-x-mark')
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['status' => 'inactive'])))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountCodes::route('/'),
            'create' => Pages\CreateDiscountCode::route('/create'),
            'view' => Pages\ViewDiscountCode::route('/{record}'),
            'edit' => Pages\EditDiscountCode::route('/{record}/edit'),
        ];
    }
}
