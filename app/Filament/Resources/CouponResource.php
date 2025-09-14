<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.coupon.basic_information'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('admin.coupon.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('SUMMER2024'),
                        
                        TextInput::make('name')
                            ->label(__('admin.coupon.name'))
                            ->required()
                            ->maxLength(255),
                        
                        Textarea::make('description')
                            ->label(__('admin.coupon.description'))
                            ->rows(3),
                        
                        Select::make('type')
                            ->label(__('admin.coupon.type'))
                            ->options([
                                'percentage' => __('admin.coupon.type_percentage'),
                                'fixed' => __('admin.coupon.type_fixed'),
                                'free_shipping' => __('admin.coupon.type_free_shipping'),
                            ])
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.coupon.discount_settings'))
                    ->schema([
                        TextInput::make('value')
                            ->label(__('admin.coupon.value'))
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                $type = $get('type');
                                if ($type === 'percentage') {
                                    $set('value', min(100, max(0, (float) $state)));
                                }
                            })
                            ->suffix(function (Forms\Get $get) {
                                return $get('type') === 'percentage' ? '%' : '€';
                            }),
                        
                        TextInput::make('minimum_amount')
                            ->label(__('admin.coupon.minimum_amount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        
                        TextInput::make('maximum_discount')
                            ->label(__('admin.coupon.maximum_discount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'percentage'),
                    ])
                    ->columns(3),
                
                Section::make(__('admin.coupon.usage_limits'))
                    ->schema([
                        TextInput::make('usage_limit')
                            ->label(__('admin.coupon.usage_limit'))
                            ->numeric()
                            ->minValue(1),
                        
                        TextInput::make('usage_limit_per_user')
                            ->label(__('admin.coupon.usage_limit_per_user'))
                            ->numeric()
                            ->minValue(1),
                        
                        TextInput::make('used_count')
                            ->label(__('admin.coupon.used_count'))
                            ->numeric()
                            ->disabled()
                            ->default(0),
                    ])
                    ->columns(3),
                
                Section::make(__('admin.coupon.applicability'))
                    ->schema([
                        CheckboxList::make('applicable_products')
                            ->label(__('admin.coupon.applicable_products'))
                            ->relationship('products', 'name')
                            ->searchable()
                            ->preload(),
                        
                        CheckboxList::make('applicable_categories')
                            ->label(__('admin.coupon.applicable_categories'))
                            ->relationship('categories', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.coupon.schedule'))
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label(__('admin.coupon.starts_at'))
                            ->default(now()),
                        
                        DatePicker::make('expires_at')
                            ->label(__('admin.coupon.expires_at'))
                            ->after('starts_at'),
                        
                        Toggle::make('is_active')
                            ->label(__('admin.coupon.is_active'))
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.coupon.id'))
                    ->sortable(),
                
                TextColumn::make('code')
                    ->label(__('admin.coupon.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                TextColumn::make('name')
                    ->label(__('admin.coupon.name'))
                    ->searchable()
                    ->sortable(),
                
                BadgeColumn::make('type')
                    ->label(__('admin.coupon.type'))
                    ->colors([
                        'primary' => 'percentage',
                        'secondary' => 'fixed',
                        'success' => 'free_shipping',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => __('admin.coupon.type_percentage'),
                        'fixed' => __('admin.coupon.type_fixed'),
                        'free_shipping' => __('admin.coupon.type_free_shipping'),
                        default => $state,
                    }),
                
                TextColumn::make('value')
                    ->label(__('admin.coupon.value'))
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'percentage') {
                            return $state . '%';
                        } elseif ($record->type === 'free_shipping') {
                            return __('admin.coupon.free_shipping');
                        }
                        return '€' . number_format($state, 2);
                    })
                    ->sortable(),
                
                TextColumn::make('minimum_amount')
                    ->label(__('admin.coupon.minimum_amount'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('usage_limit')
                    ->label(__('admin.coupon.usage_limit'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('used_count')
                    ->label(__('admin.coupon.used_count'))
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('starts_at')
                    ->label(__('admin.coupon.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('expires_at')
                    ->label(__('admin.coupon.expires_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('admin.coupon.is_active'))
                    ->boolean(),
                
                TextColumn::make('created_at')
                    ->label(__('admin.coupon.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('admin.coupon.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.coupon.type'))
                    ->options([
                        'percentage' => __('admin.coupon.type_percentage'),
                        'fixed' => __('admin.coupon.type_fixed'),
                        'free_shipping' => __('admin.coupon.type_free_shipping'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('admin.coupon.is_active')),
                
                Tables\Filters\Filter::make('expired')
                    ->label(__('admin.coupon.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now())),
                
                Tables\Filters\Filter::make('active')
                    ->label(__('admin.coupon.active'))
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)
                        ->where('starts_at', '<=', now())
                        ->where(function ($q) {
                            $q->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                        })),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
