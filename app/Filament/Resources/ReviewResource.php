<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use UnitEnum;
use BackedEnum;
use App\Enums\NavigationGroup;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use BackedEnum;
use EncoreDigitalGroup\Filament\Helpers\InputTypes\Select\NumericScale;
use EncoreDigitalGroup\Filament\Helpers\InputTypes\Select\Select as SelectInput;
use EncoreDigitalGroup\Filament\Helpers\InputTypes\Text\TextInput as TextInputInput;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

use BackedEnum;
use UnitEnum;
final class ReviewResource extends Resource
{
    use HasNav;

    protected static ?string $model = Review::class;
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    /** @var string|\UnitEnum|null */
    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::ContentManagement;

    public static function getNavigationGroup(): ?string
    {
        // Convert enum-backed navigation groups into translated labels automatically.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

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
                        SelectInput::make('product_id', __('reviews.fields.product_id'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        SelectInput::make('user_id', __('reviews.fields.user_id'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInputInput::make('reviewer_name', __('reviews.fields.reviewer_name'))
                            ->columnSpan(1)
                            ->required()
                            ->maxLength(255),
                        TextInputInput::make('reviewer_email', __('reviews.fields.reviewer_email'))
                            ->columnSpan(1)
                            ->email()
                            ->required()
                            ->maxLength(255),
                        NumericScale::make('rating', __('reviews.fields.rating'))
                            ->required(),
                    ]),
                Section::make(__('reviews.sections.content'))
                    ->description(__('reviews.sections.content_description'))
                    ->columns(1)
                    ->schema([
                        TextInputInput::make('title', __('reviews.fields.title'))
                            ->required()
                            ->maxLength(255),
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
                        TextInputInput::make('locale', __('reviews.fields.locale'))
                            ->columnSpan(1)
                            ->default('lt')
                            ->maxLength(10),
                    ]),
                Section::make(__('reviews.sections.advanced'))
                    ->description(__('reviews.sections.advanced_description'))
                    ->collapsible()
                    ->schema([
                        TextInputInput::make('metadata', __('reviews.fields.metadata'))
                            ->json()
                            ->columnSpanFull()
                            ->placeholder(__('reviews.placeholders.metadata_json')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
                    // Provide a translated fallback whenever the rating is missing to avoid formatting crashes.
                    ->formatStateUsing(fn (?int $state): string => $state !== null && $state > 0 ? str_repeat('⭐', $state) : __('reviews.placeholders.no_rating')),
                BadgeColumn::make('status')
                    ->label(__('reviews.fields.status'))
                    ->getStateUsing(fn (Review $record): string => $record->getStatus())
                    ->colors([
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending'  => 'warning',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("reviews.status.{$state}")),
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
                    ->query(fn (Builder $query): Builder => $query->where('rating', '>=', 4)),
                Filter::make('low_rated')
                    ->label(__('reviews.filters.low_rated'))
                    ->query(fn (Builder $query): Builder => $query->where('rating', '<=', 2)),
                Filter::make('recent')
                    ->label(__('reviews.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('approve')
                    ->label(__('reviews.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record): bool => $record->canBeApproved())
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
                    ->visible(fn (Review $record): bool => $record->canBeRejected())
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
                    ->visible(fn (Review $record): bool => $record->canBeFeatured())
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
                    ->visible(fn (Review $record): bool => $record->canBeUnfeatured())
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
                            foreach ($records as $record) {
                                if ($record instanceof Review) {
                                    $record->approve();
                                }
                            }

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
                            foreach ($records as $record) {
                                if ($record instanceof Review) {
                                    $record->reject();
                                }
                            }

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
                            foreach ($records as $record) {
                                if ($record instanceof Review) {
                                    $record->update(['is_featured' => true]);
                                }
                            }

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
                            foreach ($records as $record) {
                                if ($record instanceof Review) {
                                    $record->update(['is_featured' => false]);
                                }
                            }

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
        // Provide the infolist schema using the Filament v4 return type.
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
                            ->color(fn (?int $state): string => match ($state) {
                                1, 2 => 'danger',
                                3 => 'warning',
                                4, 5 => 'success',
                                default => 'gray',
                            })
                            // Mirror the table fallback so infolists never attempt to repeat a star with a null value.
                            ->formatStateUsing(fn (?int $state): string => $state !== null && $state > 0 ? str_repeat('⭐', $state) : __('reviews.placeholders.no_rating')),
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
                            ->state(fn (Review $record): string => $record->getStatus())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending'  => 'warning',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("reviews.status.{$state}")),
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
                            ->formatStateUsing(function ($state): string {
                                if (is_array($state)) {
                                    $encoded = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                                    return $encoded !== false ? $encoded : '';
                                }

                                if (is_scalar($state) || $state === null) {
                                    return (string) $state;
                                }

                                $encoded = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                                return $encoded !== false ? $encoded : '';
                            })
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

    /**
     * @return Builder<Review>
     */
    public static function getEloquentQuery(): Builder
    {
        return Review::withoutGlobalScopes();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view'   => Pages\ViewReview::route('/{record}'),
            'edit'   => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'content', 'reviewer_name', 'reviewer_email'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) Review::count();

        return $count > 0 ? (string) $count : null;
    }
}