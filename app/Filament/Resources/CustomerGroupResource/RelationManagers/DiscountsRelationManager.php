<?php

declare (strict_types=1);
namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * DiscountsRelationManager
 * 
 * Filament v4 resource for DiscountsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';
    protected static ?string $title = 'customer_groups.relation_discounts';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('code')->required()->maxLength(50)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('discounts.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('code')->label(__('discounts.code'))->searchable()->sortable()->badge()->color('primary'), Tables\Columns\TextColumn::make('type')->label(__('discounts.type'))->badge()->color(fn(string $state): string => match ($state) {
            'percentage' => 'success',
            'fixed' => 'warning',
            'free_shipping' => 'info',
            default => 'gray',
        }), Tables\Columns\TextColumn::make('value')->label(__('discounts.value'))->numeric()->suffix(fn($record) => $record->type === 'percentage' ? '%' : '€'), Tables\Columns\IconColumn::make('is_active')->label(__('discounts.is_active'))->boolean()])->filters([Tables\Filters\SelectFilter::make('type')->label(__('discounts.type'))->options(['percentage' => __('discounts.percentage'), 'fixed' => __('discounts.fixed'), 'free_shipping' => __('discounts.free_shipping')]), Tables\Filters\TernaryFilter::make('is_active')->label(__('discounts.is_active'))])->headerActions([Tables\Actions\AttachAction::make()->label(__('customer_groups.attach_discount'))])->actions([Tables\Actions\DetachAction::make()->label(__('customer_groups.detach_discount'))])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()->label(__('customer_groups.detach_selected'))])]);
    }
}