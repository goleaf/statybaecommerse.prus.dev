<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\Widgets;
use App\Models\Review;
use App\Services\MultiLanguageTabService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\IconEntry;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use UnitEnum;

final /**
 * ReviewResource
 * 
 * Filament resource for admin panel management.
 */
class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.reviews.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.reviews.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.reviews.review');
    }

    public static function form(Schema $schema): Schema
    {
        return $form
            ->components([
                // Review Settings (Non-translatable)
                Section::make(__('admin.reviews.review_settings'))
                    ->description(__('admin.reviews.review_settings_description'))
                    ->components([
                        Forms\Components\Select::make('product_id')
                            ->label(__('admin.reviews.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label(__('admin.reviews.reviewer'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.reviews.reviewer_name'))
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('admin.reviews.reviewer_email'))
                                    ->email()
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('rating')
                            ->label(__('admin.reviews.rating'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->default(5)
                            ->helperText(__('admin.reviews.rating_help')),

                        Forms\Components\Select::make('locale')
                            ->label(__('admin.reviews.locale'))
                            ->options([
                                'en' => 'English',
                                'lt' => 'Lietuvių',
                            ])
                            ->default('en')
                            ->required(),

                        Forms\Components\Toggle::make('is_approved')
                            ->label(__('admin.reviews.approved'))
                            ->default(false)
                            ->helperText(__('admin.reviews.approved_help')),

                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('admin.reviews.featured'))
                            ->default(false)
                            ->helperText(__('admin.reviews.featured_help')),
                    ])
                    ->columns(3),

                // Multilanguage content
                Tabs::make('review_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'review_information' => [
                                'title' => [
                                    'type' => 'text',
                                    'label' => __('admin.reviews.review_title'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'comment' => [
                                    'type' => 'textarea',
                                    'label' => __('admin.reviews.review_comment'),
                                    'required' => true,
                                    'maxLength' => 2000,
                                    'rows' => 5,
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('review_tab'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $infolist
            ->components([
                Section::make(__('admin.reviews.review_information'))
                    ->components([
                        TextEntry::make('product.name')
                            ->label(__('admin.reviews.product')),
                        TextEntry::make('user.name')
                            ->label(__('admin.reviews.reviewer')),
                        TextEntry::make('reviewer_name')
                            ->label(__('admin.reviews.reviewer_name')),
                        TextEntry::make('reviewer_email')
                            ->label(__('admin.reviews.reviewer_email')),
                        TextEntry::make('rating')
                            ->label(__('admin.reviews.rating'))
                            ->badge()
                            ->color(fn (int $state): string => match ($state) {
                                1, 2 => 'danger',
                                3 => 'warning',
                                4, 5 => 'success',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make(__('admin.reviews.review_content'))
                    ->components([
                        TextEntry::make('title')
                            ->label(__('admin.reviews.review_title')),
                        TextEntry::make('comment')
                            ->label(__('admin.reviews.review_comment'))
                            ->html(),
                    ])
                    ->columns(1),

                Section::make(__('admin.reviews.review_status'))
                    ->components([
                        IconEntry::make('is_approved')
                            ->label(__('admin.reviews.approved'))
                            ->boolean(),
                        IconEntry::make('is_featured')
                            ->label(__('admin.reviews.featured'))
                            ->boolean(),
                        TextEntry::make('locale')
                            ->label(__('admin.reviews.locale'))
                            ->badge(),
                        TextEntry::make('approved_at')
                            ->label(__('admin.reviews.approved_at'))
                            ->dateTime(),
                        TextEntry::make('rejected_at')
                            ->label(__('admin.reviews.rejected_at'))
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make(__('translations.timestamps'))
                    ->components([
                        TextEntry::make('created_at')
                            ->label(__('translations.created_at'))
                            ->date('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label(__('translations.updated_at'))
                            ->date('Y-m-d H:i:s'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.reviews.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('reviewer_name')
                    ->label(__('admin.reviews.reviewer'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('rating')
                    ->label(__('admin.reviews.rating'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1, 2 => 'danger',
                        3 => 'warning',
                        4, 5 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.reviews.review_title'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label(__('admin.reviews.review_comment'))
                    ->limit(100)
                    ->toggleable()
                    ->html(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('admin.reviews.approved'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.reviews.featured'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('locale')
                    ->label(__('admin.reviews.locale'))
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('admin.reviews.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('rating')
                    ->label(__('admin.reviews.rating'))
                    ->options([
                        1 => '1 ⭐',
                        2 => '2 ⭐',
                        3 => '3 ⭐',
                        4 => '4 ⭐',
                        5 => '5 ⭐',
                    ]),

                Tables\Filters\Filter::make('approved')
                    ->label(__('admin.reviews.approved_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_approved', true)),

                Tables\Filters\Filter::make('pending')
                    ->label(__('admin.reviews.pending_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_approved', false)->whereNull('rejected_at')),

                Tables\Filters\Filter::make('featured')
                    ->label(__('admin.reviews.featured_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true)),

                Tables\Filters\Filter::make('high_rated')
                    ->label(__('admin.reviews.high_rated_only'))
                    ->query(fn (Builder $query): Builder => $query->where('rating', '>=', 4)),

                Tables\Filters\Filter::make('low_rated')
                    ->label(__('admin.reviews.low_rated_only'))
                    ->query(fn (Builder $query): Builder => $query->where('rating', '<=', 2)),

                Tables\Filters\Filter::make('recent')
                    ->label(__('admin.reviews.recent_only'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label(__('admin.reviews.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record): bool => !$record->is_approved)
                    ->action(fn (Review $record) => $record->approve())
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('reject')
                    ->label(__('admin.reviews.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->is_approved)
                    ->action(fn (Review $record) => $record->reject())
                    ->requiresConfirmation(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('admin.reviews.approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->approve())
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('reject')
                        ->label(__('admin.reviews.reject_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->reject())
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label(__('admin.reviews.feature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('info')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label(__('admin.reviews.unfeature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(fn ($records) => $records->each->update(['is_featured' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ReviewStatsWidget::class,
            Widgets\ReviewRatingDistributionWidget::class,
            Widgets\ReviewApprovalWidget::class,
            Widgets\RecentReviewsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->title ?: $record->product->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('admin.reviews.product') => $record->product->name,
            __('admin.reviews.rating') => str_repeat('⭐', $record->rating),
            __('admin.reviews.reviewer') => $record->reviewer_name,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'comment', 'reviewer_name'];
    }
}
