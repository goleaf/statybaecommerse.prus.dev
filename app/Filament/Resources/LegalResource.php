<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LegalResource\Pages;
use App\Models\Legal;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use BackedEnum;
/**
 * LegalResource
 * 
 * Filament v4 resource for LegalResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property string|BackedEnum|null $navigationIcon
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 3;
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Content->label();
    }
    protected static ?string $recordTitleAttribute = 'key';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Tabs::make('Legal Document')->tabs([Tab::make('Basic Information')->schema([Section::make('Document Details')->schema([Grid::make(2)->schema([TextInput::make('key')->label(__('admin.legal.key'))->required()->unique(ignoreRecord: true)->maxLength(255)->helperText(__('admin.legal.key_help')), Select::make('type')->label(__('admin.legal.type'))->options(Legal::getTypes())->required()->default('legal_document')->searchable()]), Grid::make(3)->schema([Toggle::make('is_enabled')->label(__('admin.legal.is_enabled'))->default(true)->helperText(__('admin.legal.is_enabled_help')), Toggle::make('is_required')->label(__('admin.legal.is_required'))->default(false)->helperText(__('admin.legal.is_required_help')), TextInput::make('sort_order')->label(__('admin.legal.sort_order'))->numeric()->default(0)->helperText(__('admin.legal.sort_order_help'))]), DateTimePicker::make('published_at')->label(__('admin.legal.published_at'))->helperText(__('admin.legal.published_at_help'))])->columns(1)]), Tab::make('Translations')->schema([Section::make('Lithuanian (LT)')->schema([TextInput::make('translations.lt.title')->label(__('admin.legal.title'))->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(function (Forms\Set $set, $state) {
            if ($state) {
                $set('translations.lt.slug', Str::slug($state) . '-lt');
            }
        }), TextInput::make('translations.lt.slug')->label(__('admin.legal.slug'))->required()->maxLength(255)->unique('legal_translations', 'slug', ignoreRecord: true), RichEditor::make('translations.lt.content')->label(__('admin.legal.content'))->required()->columnSpanFull()->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList', 'h2', 'h3', 'blockquote', 'codeBlock']), Grid::make(2)->schema([TextInput::make('translations.lt.seo_title')->label(__('admin.legal.seo_title'))->maxLength(255)->helperText(__('admin.legal.seo_title_help')), Textarea::make('translations.lt.seo_description')->label(__('admin.legal.seo_description'))->maxLength(500)->rows(3)->helperText(__('admin.legal.seo_description_help'))])])->columns(1), Section::make('English (EN)')->schema([TextInput::make('translations.en.title')->label(__('admin.legal.title'))->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(function (Forms\Set $set, $state) {
            if ($state) {
                $set('translations.en.slug', Str::slug($state) . '-en');
            }
        }), TextInput::make('translations.en.slug')->label(__('admin.legal.slug'))->required()->maxLength(255)->unique('legal_translations', 'slug', ignoreRecord: true), RichEditor::make('translations.en.content')->label(__('admin.legal.content'))->required()->columnSpanFull()->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList', 'h2', 'h3', 'blockquote', 'codeBlock']), Grid::make(2)->schema([TextInput::make('translations.en.seo_title')->label(__('admin.legal.seo_title'))->maxLength(255)->helperText(__('admin.legal.seo_title_help')), Textarea::make('translations.en.seo_description')->label(__('admin.legal.seo_description'))->maxLength(500)->rows(3)->helperText(__('admin.legal.seo_description_help'))])])->columns(1)]), Tab::make('Metadata')->schema([Section::make('Additional Information')->schema([KeyValue::make('meta_data')->label(__('admin.legal.meta_data'))->keyLabel(__('admin.legal.meta_key'))->valueLabel(__('admin.legal.meta_value'))->helperText(__('admin.legal.meta_data_help'))])->columns(1)])])->columnSpanFull()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('key')->label(__('admin.legal.key'))->searchable()->sortable()->copyable(), TextColumn::make('type')->label(__('admin.legal.type'))->badge()->color(fn(string $state): string => match ($state) {
            'privacy_policy' => 'danger',
            'terms_of_use' => 'warning',
            'refund_policy' => 'info',
            'shipping_policy' => 'success',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => Legal::getTypes()[$state] ?? $state), TextColumn::make('translations.title')->label(__('admin.legal.title'))->getStateUsing(function (Legal $record): string {
            $translation = $record->translations()->where('locale', app()->getLocale())->first();
            return $translation?->title ?? $record->key;
        })->searchable()->sortable(), IconColumn::make('is_enabled')->label(__('admin.legal.is_enabled'))->boolean()->sortable(), IconColumn::make('is_required')->label(__('admin.legal.is_required'))->boolean()->sortable(), TextColumn::make('status')->label(__('admin.legal.status'))->badge()->color(fn(string $state): string => match ($state) {
            'published' => 'success',
            'draft' => 'warning',
            'disabled' => 'danger',
            default => 'gray',
        }), TextColumn::make('sort_order')->label(__('admin.legal.sort_order'))->sortable()->alignCenter(), TextColumn::make('published_at')->label(__('admin.legal.published_at'))->dateTime()->sortable()->toggleable(), TextColumn::make('created_at')->label(__('admin.legal.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('admin.legal.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('type')->label(__('admin.legal.type'))->options(Legal::getTypes())->multiple(), TernaryFilter::make('is_enabled')->label(__('admin.legal.is_enabled')), TernaryFilter::make('is_required')->label(__('admin.legal.is_required')), SelectFilter::make('status')->label(__('admin.legal.status'))->options(['published' => __('admin.legal.status_published'), 'draft' => __('admin.legal.status_draft'), 'disabled' => __('admin.legal.status_disabled')])->query(function (Builder $query, array $data): Builder {
            return match ($data['value']) {
                'published' => $query->where('is_enabled', true)->whereNotNull('published_at'),
                'draft' => $query->whereNull('published_at'),
                'disabled' => $query->where('is_enabled', false),
                default => $query,
            };
        })])->actions([])->bulkActions([])->defaultSort('sort_order')->reorderable('sort_order');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListLegals::route('/'), 'create' => Pages\CreateLegal::route('/create'), 'edit' => Pages\EditLegal::route('/{record}/edit')];
    }
    /**
     * Handle getNavigationBadge functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
    /**
     * Handle getNavigationBadgeColor functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_enabled', false)->count() > 0 ? 'warning' : 'primary';
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['translations']);
    }
}