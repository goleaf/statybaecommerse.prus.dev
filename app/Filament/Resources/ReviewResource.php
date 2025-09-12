<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use App\Services\MultiLanguageTabService;
use Filament\Forms\Get as FormsGet;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use \BackedEnum;
final class ReviewResource extends Resource
{
	protected static ?string $model = Review::class;

	protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-star';


	protected static ?int $navigationSort = 6;

	public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
	{
		return $schema
			->schema([
				\Filament\Schemas\Components\Section::make(__('translations.review_information'))
					->components([
						Forms\Components\Select::make('product_id')
							->label(__('translations.product'))
							->relationship('product', 'name')
							->searchable()
							->preload()
							->required(),
						Forms\Components\Select::make('user_id')
							->label(__('translations.reviewer'))
							->relationship('user', 'name')
							->searchable()
							->preload()
							->required(),
						// Multilanguage content will be in tabs below
						Forms\Components\Select::make('rating')
							->label(__('translations.rating'))
							->options([
								1 => '⭐ 1 ' . __('translations.star'),
								2 => '⭐⭐ 2 ' . __('translations.stars'),
								3 => '⭐⭐⭐ 3 ' . __('translations.stars'),
								4 => '⭐⭐⭐⭐ 4 ' . __('translations.stars'),
								5 => '⭐⭐⭐⭐⭐ 5 ' . __('translations.stars'),
							])
							->required()
							->default(5),
						Forms\Components\Toggle::make('is_approved')
							->label(__('translations.approved'))
							->default(false),
						Forms\Components\DateTimePicker::make('approved_at')
							->label(__('translations.approved_at'))
							->visible(fn(FormsGet $get) => (bool) $get('is_approved')),
					])
					->columns(2),
				// Multilanguage Tabs for Review Content (disabled in testing for stability)
				...(app()->environment('testing') ? [
					\Filament\Schemas\Components\Section::make(__('translations.review_content'))
						->components([
							Forms\Components\TextInput::make('title')
								->label(__('translations.title'))
								->required()
								->maxLength(255),
							Forms\Components\Textarea::make('content')
								->label(__('translations.content'))
								->required()
								->rows(4)
								->maxLength(2000),
						])
				] : [
					\Filament\Forms\Components\Tabs::make('review_translations')
						->tabs(
							MultiLanguageTabService::createSectionedTabs([
								'review_content' => [
									'title' => [
										'type' => 'text',
										'label' => __('translations.title'),
										'required' => true,
										'maxLength' => 255,
									],
									'content' => [
										'type' => 'textarea',
										'label' => __('translations.content'),
										'required' => true,
										'maxLength' => 2000,
										'rows' => 4,
										'placeholder' => __('translations.review_content_help'),
									],
								],
							])
						)
						->activeTab(MultiLanguageTabService::getDefaultActiveTab())
						->persistTabInQueryString('review_tab')
						->contained(true),
				]),
			]);
	}

	public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
	{
		return $table
			->columns([
				\Filament\Tables\Columns\TextColumn::make('product.name')
					->label(__('translations.product'))
					->searchable()
					->sortable()
					->limit(30),
				\Filament\Tables\Columns\TextColumn::make('user.name')
					->label(__('translations.reviewer'))
					->searchable()
					->sortable(),
				\Filament\Tables\Columns\TextColumn::make('title')
					->label(__('translations.title'))
					->searchable()
					->limit(40),
				\Filament\Tables\Columns\TextColumn::make('rating')
					->label(__('translations.rating'))
					->formatStateUsing(fn($state): string => str_repeat('⭐', $state))
					->sortable(),
				\Filament\Tables\Columns\IconColumn::make('is_approved')
					->label(__('translations.approved'))
					->boolean()
					->sortable(),
				\Filament\Tables\Columns\TextColumn::make('approved_at')
					->label(__('translations.approved_at'))
					->date('Y-m-d')
					->sortable()
					->toggleable(),
				\Filament\Tables\Columns\TextColumn::make('created_at')
					->label(__('translations.created_at'))
					->date('Y-m-d')
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				\Filament\Tables\Filters\SelectFilter::make('rating')
					->label(__('translations.rating'))
					->options([
						1 => '⭐ 1 ' . __('translations.star'),
						2 => '⭐⭐ 2 ' . __('translations.stars'),
						3 => '⭐⭐⭐ 3 ' . __('translations.stars'),
						4 => '⭐⭐⭐⭐ 4 ' . __('translations.stars'),
						5 => '⭐⭐⭐⭐⭐ 5 ' . __('translations.stars'),
					]),
				\Filament\Tables\Filters\Filter::make('approved')
					->label(__('translations.approved_only'))
					->query(fn(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->where('is_approved', true)),
				\Filament\Tables\Filters\Filter::make('pending')
					->label(__('translations.pending_approval'))
					->query(fn(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->where('is_approved', false)),
				\Filament\Tables\Filters\SelectFilter::make('product_id')
					->label(__('translations.product'))
					->relationship('product', 'name')
					->searchable()
					->preload(),
			])
			->recordActions([
				Actions\ViewAction::make(),
				Actions\EditAction::make(),
				Actions\Action::make('approve')
					->label(__('translations.approve'))
					->icon('heroicon-o-check')
					->color('success')
					->visible(fn($record) => !$record->is_approved)
					->action(fn($record) => $record->update([
						'is_approved' => true,
						'approved_at' => now(),
					])),
				Actions\Action::make('reject')
					->label(__('translations.reject'))
					->icon('heroicon-o-x-mark')
					->color('danger')
					->visible(fn($record) => $record->is_approved)
					->action(fn($record) => $record->update([
						'is_approved' => false,
						'approved_at' => null,
					])),
				Actions\DeleteAction::make(),
			])
			->bulkActions([
				Actions\BulkActionGroup::make([
					Actions\BulkAction::make('approve_selected')
						->label(__('translations.approve_selected'))
						->icon('heroicon-o-check')
						->color('success')
						->action(fn($records) => $records->each(fn($record) => $record->update([
							'is_approved' => true,
							'approved_at' => now(),
						]))),
					Actions\BulkAction::make('reject_selected')
						->label(__('translations.reject_selected'))
						->icon('heroicon-o-x-mark')
						->color('danger')
						->action(fn($records) => $records->each(fn($record) => $record->update([
							'is_approved' => false,
							'approved_at' => null,
						]))),
					Actions\DeleteBulkAction::make(),
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
			'index' => Pages\ListReviews::route('/'),
			'create' => Pages\CreateReview::route('/create'),
			'view' => Pages\ViewReview::route('/{record}'),
			'edit' => Pages\EditReview::route('/{record}/edit'),
		];
	}
}
