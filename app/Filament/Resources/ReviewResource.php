<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    public static function getNavigationLabel(): string
    {
        return __('reviews.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('reviews.plural');
    }

    public static function getModelLabel(): string
    {
        return __('reviews.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('reviews.sections.basic_info'))
                    ->description(__('reviews.sections.basic_info_description'))
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label(__('reviews.fields.product_id'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label(__('reviews.fields.user_id'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('reviewer_name')
                            ->label(__('reviews.fields.reviewer_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('reviewer_email')
                            ->label(__('reviews.fields.reviewer_email'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('rating')
                            ->label(__('reviews.fields.rating'))
                            ->required()
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ]),
                    ]),
                Section::make(__('reviews.sections.content'))
                    ->description(__('reviews.sections.content_description'))
                    ->columns(1)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('reviews.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('content')
                            ->label(__('reviews.fields.content'))
                            ->required()
                            ->maxLength(65535)
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('reviews.sections.status'))
                    ->description(__('reviews.sections.status_description'))
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_approved')
                            ->label(__('reviews.fields.is_approved'))
                            ->inline(false)
                            ->default(false),
                        Toggle::make('is_featured')
                            ->label(__('reviews.fields.is_featured'))
                            ->inline(false)
                            ->default(false),
                        TextInput::make('locale')
                            ->label(__('reviews.fields.locale'))
                            ->default('lt')
                            ->maxLength(10),
                    ]),
                Section::make(__('reviews.sections.advanced'))
                    ->description(__('reviews.sections.advanced_description'))
                    ->collapsible()
                    ->schema([
                        TextInput::make('metadata')
                            ->label(__('reviews.fields.metadata'))
                            ->json()
                            ->columnSpanFull()
                            ->placeholder(__('reviews.placeholders.metadata_json')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('reviews.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),
                TextColumn::make('product.name')
                    ->label(__('reviews.fields.product_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->badge()
                    ->color('info'),
                TextColumn::make('user.name')
                    ->label(__('reviews.fields.user_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder(__('reviews.placeholders.guest_user')),
                TextColumn::make('reviewer_name')
                    ->label(__('reviews.fields.reviewer_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('rating')
                    ->label(__('reviews.fields.rating'))
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn(int $state): string => str_repeat('⭐', $state)),
                BadgeColumn::make('status')
                    ->label(__('reviews.fields.status'))
                    ->getStateUsing(fn(Review $record): string => $record->getStatus())
                    ->colors([
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("reviews.status.{$state}")),
                IconColumn::make('is_approved')
                    ->label(__('reviews.fields.is_approved'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('reviews.fields.is_featured'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('locale')
                    ->label(__('reviews.fields.locale'))
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('reviews.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('reviews.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label(__('reviews.filters.is_approved'))
                    ->boolean(),
                TernaryFilter::make('is_featured')
                    ->label(__('reviews.filters.is_featured'))
                    ->boolean(),
                SelectFilter::make('rating')
                    ->label(__('reviews.filters.rating'))
                    ->options([
                        1 => __('reviews.filters.rating_1'),
                        2 => __('reviews.filters.rating_2'),
                        3 => __('reviews.filters.rating_3'),
                        4 => __('reviews.filters.rating_4'),
                        5 => __('reviews.filters.rating_5'),
                    ]),
                SelectFilter::make('product_id')
                    ->label(__('reviews.filters.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('reviews.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('locale')
                    ->label(__('reviews.filters.locale'))
                    ->options([
                        'lt' => __('reviews.filters.locale_lt'),
                        'en' => __('reviews.filters.locale_en'),
                    ]),
                Filter::make('high_rated')
                    ->label(__('reviews.filters.high_rated'))
                    ->query(fn(Builder $query): Builder => $query->where('rating', '>=', 4)),
                Filter::make('low_rated')
                    ->label(__('reviews.filters.low_rated'))
                    ->query(fn(Builder $query): Builder => $query->where('rating', '<=', 2)),
                Filter::make('recent')
                    ->label(__('reviews.filters.recent'))
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('approve')
                    ->label(__('reviews.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Review $record): bool => $record->canBeApproved())
                    ->action(function (Review $record): void {
                        $record->approve();
                        Notification::make()
                            ->title(__('reviews.notifications.approved_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('reject')
                    ->label(__('reviews.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Review $record): bool => $record->canBeRejected())
                    ->action(function (Review $record): void {
                        $record->reject();
                        Notification::make()
                            ->title(__('reviews.notifications.rejected_successfully'))
                            ->warning()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('feature')
                    ->label(__('reviews.actions.feature'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(Review $record): bool => $record->canBeFeatured())
                    ->action(function (Review $record): void {
                        $record->update(['is_featured' => true]);
                        Notification::make()
                            ->title(__('reviews.notifications.featured_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unfeature')
                    ->label(__('reviews.actions.unfeature'))
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn(Review $record): bool => $record->canBeUnfeatured())
                    ->action(function (Review $record): void {
                        $record->update(['is_featured' => false]);
                        Notification::make()
                            ->title(__('reviews.notifications.unfeatured_successfully'))
                            ->info()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('reviews.actions.approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->approve();
                            Notification::make()
                                ->title(__('reviews.notifications.bulk_approved_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('reject')
                        ->label(__('reviews.actions.reject_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->reject();
                            Notification::make()
                                ->title(__('reviews.notifications.bulk_rejected_successfully'))
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('feature')
                        ->label(__('reviews.actions.feature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()
                                ->title(__('reviews.notifications.bulk_featured_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unfeature')
                        ->label(__('reviews.actions.unfeature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()
                                ->title(__('reviews.notifications.bulk_unfeatured_successfully'))
                                ->info()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make(__('reviews.sections.basic_info'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('reviews.fields.title'))
                            ->weight('medium'),
                        TextEntry::make('product.name')
                            ->label(__('reviews.fields.product_name'))
                            ->badge()
                            ->color('info'),
                        TextEntry::make('user.name')
                            ->label(__('reviews.fields.user_name'))
                            ->placeholder(__('reviews.placeholders.guest_user')),
                        TextEntry::make('reviewer_name')
                            ->label(__('reviews.fields.reviewer_name')),
                        TextEntry::make('reviewer_email')
                            ->label(__('reviews.fields.reviewer_email')),
                        TextEntry::make('rating')
                            ->label(__('reviews.fields.rating'))
                            ->badge()
                            ->color(fn(int $state): string => match ($state) {
                                1, 2 => 'danger',
                                3 => 'warning',
                                4, 5 => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(int $state): string => str_repeat('⭐', $state)),
                    ])
                    ->columns(2),
                InfolistSection::make(__('reviews.sections.content'))
                    ->schema([
                        TextEntry::make('content')
                            ->label(__('reviews.fields.content'))
                            ->columnSpanFull()
                            ->placeholder(__('reviews.placeholders.no_content')),
                    ]),
                InfolistSection::make(__('reviews.sections.status'))
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('reviews.fields.status'))
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => __("reviews.status.{$state}")),
                        IconEntry::make('is_approved')
                            ->label(__('reviews.fields.is_approved'))
                            ->boolean(),
                        IconEntry::make('is_featured')
                            ->label(__('reviews.fields.is_featured'))
                            ->boolean(),
                        TextEntry::make('locale')
                            ->label(__('reviews.fields.locale'))
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2),
                InfolistSection::make(__('reviews.sections.advanced'))
                    ->collapsible()
                    ->schema([
                        TextEntry::make('metadata')
                            ->label(__('reviews.fields.metadata'))
                            ->json()
                            ->placeholder(__('reviews.placeholders.no_metadata')),
                    ]),
                InfolistSection::make(__('reviews.sections.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('reviews.fields.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('reviews.fields.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'content', 'reviewer_name', 'reviewer_email'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) self::$model::count();

        return $count > 0 ? (string) $count : null;
    }
}
