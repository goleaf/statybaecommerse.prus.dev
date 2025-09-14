<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
/**
 * EuMembersWidget
 * 
 * Filament v4 resource for EuMembersWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EuMembersWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.countries.widgets.eu_members';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Country::query()->where('is_eu_member', true)->where('is_active', true)->orderBy('name'))->columns([Tables\Columns\ImageColumn::make('flag')->label(__('admin.countries.fields.flag'))->disk('public')->height(20)->width(30)->circular(false)->defaultImageUrl(asset('images/no-flag.png')), Tables\Columns\TextColumn::make('translated_name')->label(__('admin.countries.fields.name'))->getStateUsing(fn(Country $record): string => ($record->trans('name') ?: $record->name) ?: '-')->searchable(['name'])->sortable()->weight('bold'), Tables\Columns\TextColumn::make('cca2')->label(__('admin.countries.fields.cca2'))->badge()->color('info'), Tables\Columns\TextColumn::make('currency_code')->label(__('admin.countries.fields.currency_code'))->badge()->color('success'), Tables\Columns\TextColumn::make('vat_rate')->label(__('admin.countries.fields.vat_rate'))->formatStateUsing(fn(?float $state): string => $state ? number_format($state, 2) . '%' : '-')->badge()->color('warning'), Tables\Columns\IconColumn::make('requires_vat')->label(__('admin.countries.fields.requires_vat'))->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')->trueColor('success')->falseColor('gray')])->paginated(false)->defaultSort('name', 'asc');
    }
}