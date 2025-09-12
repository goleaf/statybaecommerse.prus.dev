<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Services\MultiLanguageTabService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use \BackedEnum;
final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-ticket';


    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Coupon Settings (Non-translatable)
                Section::make(__('translations.coupon_settings'))
                    ->components([
                        Forms\Components\TextInput::make('code')
                            ->label(__('translations.coupon_code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Coupon::class, 'code', ignoreRecord: true)
                            ->helperText(__('translations.coupon_code_help')),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('translations.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                // Multilanguage Tabs for Coupon Content
                Tabs::make('coupon_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'coupon_information' => [
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
                                    'placeholder' => __('translations.coupon_description_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('coupon_tab')
                    ->contained(false),
                Section::make(__('admin.coupons.sections.discount_settings'))
                    ->components([
                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => __('admin.coupons.types.percentage'),
                                'fixed' => __('admin.coupons.types.fixed'),
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->suffix(fn(Forms\Get $get) => $get('type') === 'percentage' ? '%' : '€'),
                        Forms\Components\TextInput::make('minimum_amount')
                            ->numeric()
                            ->prefix('€')
                            ->label(__('admin.coupons.fields.minimum_amount')),
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->label(__('admin.coupons.fields.usage_limit')),
                    ])
                    ->columns(2),
                Section::make(__('admin.coupons.sections.validity_period'))
                    ->components([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label(__('admin.coupons.fields.starts_at')),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label(__('admin.coupons.fields.expires_at')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'primary',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn(string $state, Coupon $record): string =>
                        $record->type === 'percentage' ? $state . '%' : '€' . $state),
                Tables\Columns\TextColumn::make('minimum_amount')
                    ->money('EUR')
                    ->placeholder(__('admin.coupons.placeholders.no_minimum')),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->placeholder(__('admin.coupons.placeholders.unlimited')),
                Tables\Columns\TextColumn::make('used_count')
                    ->label(__('admin.coupons.fields.used_count')),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder(__('admin.coupons.placeholders.no_start_date')),
                Tables\Columns\TextColumn::make('expires_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder(__('admin.coupons.placeholders.no_expiry')),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => __('admin.coupons.types.percentage'),
                        'fixed' => __('admin.coupons.types.fixed'),
                    ]),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('valid_now')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->where(function (Builder $q) {
                            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function (Builder $q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        })),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
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
