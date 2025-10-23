<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AuditTrailResource\Pages;
use App\Models\AuditTrail;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class AuditTrailResource extends Resource
{
    protected static ?string $model = AuditTrail::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Security';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.audit_trails.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('event')
                    ->label(__('admin.audit_trails.fields.event'))
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('auditable_label')
                    ->label(__('admin.audit_trails.fields.auditable'))
                    ->searchable()
                    ->tooltip(fn (AuditTrail $record): string => $record->auditable_type ?? '')
                    ->wrap(),
                TextColumn::make('actor_display_name')
                    ->label(__('admin.audit_trails.fields.actor'))
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('diff_keys')
                    ->label(__('admin.audit_trails.fields.changed_fields'))
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => $state !== '' ? $state : __('admin.audit_trails.none'))
                    ->toggleable(),
                TextColumn::make('reason')
                    ->label(__('admin.audit_trails.fields.reason'))
                    ->toggleable()
                    ->wrap()
                    ->limit(50),
                TextColumn::make('request_id')
                    ->label(__('admin.audit_trails.fields.request_id'))
                    ->copyable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('admin.audit_trails.filters.event'))
                    ->options([
                        'price.updated' => __('admin.audit_trails.events.price_updated'),
                        'inventory.updated' => __('admin.audit_trails.events.inventory_updated'),
                        'admin_user.roles.updated' => __('admin.audit_trails.events.roles_updated'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (AuditTrail $record): string => self::getUrl('view', ['record' => $record]));
    }

    public static function infolist(Schema $schema): Schema
    {
        /** @var array<int, Component> $components */
        $components = [
            SchemaSection::make(__('admin.audit_trails.details'))
                ->schema([
                    TextEntry::make('created_at')
                        ->label(__('admin.audit_trails.fields.created_at'))
                        ->dateTime(),
                    TextEntry::make('event')
                        ->label(__('admin.audit_trails.fields.event')),
                    TextEntry::make('auditable_label')
                        ->label(__('admin.audit_trails.fields.auditable')),
                    TextEntry::make('actor_display_name')
                        ->label(__('admin.audit_trails.fields.actor')),
                    TextEntry::make('request_id')
                        ->label(__('admin.audit_trails.fields.request_id')),
                    TextEntry::make('reason')
                        ->label(__('admin.audit_trails.fields.reason'))
                        ->placeholder(__('admin.audit_trails.none')),
                ])
                ->columns(2),
            SchemaSection::make(__('admin.audit_trails.diff_section'))
                ->schema([
                    TextEntry::make('diff_pretty')
                        ->label(__('admin.audit_trails.fields.diff'))
                        ->formatStateUsing(fn (string $state): string => sprintf('<pre class="text-xs whitespace-pre-wrap">%s</pre>', e($state)))
                        ->html(),
                ])
                ->columns(1),
        ];

        return $schema->components($components);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditTrails::route('/'),
            'view' => Pages\ViewAuditTrail::route('/{record}'),
        ];
    }
}
