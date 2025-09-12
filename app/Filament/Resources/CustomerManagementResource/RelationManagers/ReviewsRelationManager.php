<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'admin.customers.reviews';

    protected static ?string $modelLabel = 'admin.customers.review';

    protected static ?string $pluralModelLabel = 'admin.customers.reviews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('admin.customers.fields.product'))
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('rating')
                    ->label(__('admin.customers.fields.rating'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(5),

                Forms\Components\Textarea::make('title')
                    ->label(__('admin.customers.fields.review_title'))
                    ->maxLength(255)
                    ->rows(2),

                Forms\Components\Textarea::make('content')
                    ->label(__('admin.customers.fields.review_content'))
                    ->required()
                    ->rows(4),

                Forms\Components\Toggle::make('is_approved')
                    ->label(__('admin.customers.fields.is_approved'))
                    ->helperText(__('admin.is_approved_help')),

                Forms\Components\Toggle::make('is_featured')
                    ->label(__('admin.customers.fields.is_featured'))
                    ->helperText(__('admin.is_featured_help')),

                Forms\Components\Toggle::make('is_verified_purchase')
                    ->label(__('admin.customers.fields.is_verified_purchase'))
                    ->helperText(__('admin.is_verified_purchase_help')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.customers.fields.product'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label(__('admin.customers.fields.rating'))
                    ->formatStateUsing(function (int $state): string {
                        return str_repeat('★', $state) . str_repeat('☆', 5 - $state) . " ({$state}/5)";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.customers.fields.review_title'))
                    ->searchable()
                    ->limit(50)
                    ->placeholder(__('admin.customers.no_title')),

                Tables\Columns\TextColumn::make('content')
                    ->label(__('admin.customers.fields.review_content'))
                    ->searchable()
                    ->limit(100),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('admin.customers.fields.is_approved'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.customers.fields.is_featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_verified_purchase')
                    ->label(__('admin.customers.fields.is_verified_purchase'))
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.customers.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.customers.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label(__('admin.customers.fields.rating'))
                    ->options([
                        1 => '1 ' . __('admin.star'),
                        2 => '2 ' . __('admin.stars'),
                        3 => '3 ' . __('admin.stars'),
                        4 => '4 ' . __('admin.stars'),
                        5 => '5 ' . __('admin.stars'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label(__('admin.customers.fields.is_approved'))
                    ->nullable()
                    ->trueLabel(__('admin.approved'))
                    ->falseLabel(__('admin.pending_approval')),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.customers.fields.is_featured'))
                    ->nullable()
                    ->trueLabel(__('admin.featured'))
                    ->falseLabel(__('admin.not_featured')),

                Tables\Filters\TernaryFilter::make('is_verified_purchase')
                    ->label(__('admin.customers.fields.is_verified_purchase'))
                    ->nullable()
                    ->trueLabel(__('admin.verified_purchase'))
                    ->falseLabel(__('admin.not_verified_purchase')),

                Tables\Filters\Filter::make('created_at')
                    ->label(__('admin.customers.fields.created_at'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.customers.filters.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.customers.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.customers.create_review')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('admin.actions.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.actions.delete_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}