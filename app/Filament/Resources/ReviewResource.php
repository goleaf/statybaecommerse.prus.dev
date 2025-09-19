<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Rating;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\RatingColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
/**
 * ReviewResource
 *
 * Filament v4 resource for Review management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'title';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('reviews.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('reviews.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('reviews.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
        return $schema->components([
            Section::make(__('reviews.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('product_id')
                                ->label(__('reviews.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('user_id')
                                ->label(__('reviews.user'))
                                ->relationship('user', 'name')
                        ]),
                    TextInput::make('title')
                        ->label(__('reviews.title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('content')
                        ->label(__('reviews.content'))
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            Section::make(__('reviews.rating'))
                            Rating::make('rating')
                                ->label(__('reviews.rating'))
                                ->required()
                                ->minValue(1)
                                ->maxValue(5)
                                ->default(5),
                            Toggle::make('is_approved')
                                ->label(__('reviews.is_approved'))
                                ->default(false),
            Section::make(__('reviews.additional_information'))
                            TextInput::make('pros')
                                ->label(__('reviews.pros'))
                                ->maxLength(500),
                            TextInput::make('cons')
                                ->label(__('reviews.cons'))
                    Toggle::make('is_verified_purchase')
                        ->label(__('reviews.is_verified_purchase'))
                        ->default(false)
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('reviews.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('user.name')
                    ->label(__('reviews.user'))
                TextColumn::make('title')
                    ->label(__('reviews.title'))
                    ->limit(50),
                RatingColumn::make('rating')
                    ->label(__('reviews.rating'))
                    ->sortable(),
                TextColumn::make('content')
                    ->label(__('reviews.content'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_approved')
                    ->label(__('reviews.is_approved'))
                    ->boolean()
                IconColumn::make('is_verified_purchase')
                    ->label(__('reviews.is_verified_purchase'))
                TextColumn::make('created_at')
                    ->label(__('reviews.created_at'))
                    ->dateTime()
                TextColumn::make('updated_at')
                    ->label(__('reviews.updated_at'))
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->preload(),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 ' . __('reviews.star'),
                        2 => '2 ' . __('reviews.stars'),
                        3 => '3 ' . __('reviews.stars'),
                        4 => '4 ' . __('reviews.stars'),
                        5 => '5 ' . __('reviews.stars'),
                    ]),
                TernaryFilter::make('is_approved')
                    ->trueLabel(__('reviews.approved_only'))
                    ->falseLabel(__('reviews.pending_only'))
                    ->native(false),
                TernaryFilter::make('is_verified_purchase')
                    ->trueLabel(__('reviews.verified_only'))
                    ->falseLabel(__('reviews.not_verified'))
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label(__('reviews.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Review $record): bool => !$record->is_approved)
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => true]);
                        Notification::make()
                            ->title(__('reviews.approved_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('disapprove')
                    ->label(__('reviews.disapprove'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Review $record): bool => $record->is_approved)
                        $record->update(['is_approved' => false]);
                            ->title(__('reviews.disapproved_successfully'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('reviews.approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_approved' => true]);
                            Notification::make()
                                ->title(__('reviews.bulk_approved_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('disapprove')
                        ->label(__('reviews.disapprove_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                            $records->each->update(['is_approved' => false]);
                                ->title(__('reviews.bulk_disapproved_success'))
            ->defaultSort('created_at', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
}
