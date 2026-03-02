<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Partners\PartnerResource;
use App\Models\PartnerTier;
use App\Models\Partner;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartnersRelationManager extends RelationManager
{
    protected static string $relationship = 'partners';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('messages.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label(__('messages.code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('contact_email')
                    ->label(__('messages.email'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('contact_phone')
                    ->label(__('messages.phone'))
                    ->tel()
                    ->maxLength(255),
                TextInput::make('discount_rate')
                    ->label(__('messages.discount'))
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%'),
                TextInput::make('commission_rate')
                    ->label(__('messages.admin_widgets.average_order_value'))
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%'),
                Select::make('tier_id')
                    ->label(__('messages.partner_tiers'))
                    ->options(static fn (): array => PartnerTier::query()
                        ->orderBy('priority')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                KeyValue::make('metadata')
                    ->label(__('admin.labels.metadata'))
                    ->nullable()
                    ->columnSpanFull(),
                Toggle::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('contact_phone')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('partnerTier.name')
                    ->label(__('messages.partner_tiers'))
                    ->sortable(),
                TextColumn::make('discount_rate')
                    ->label(__('messages.discount'))
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->label(__('messages.admin_widgets.average_order_value'))
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->sortable()
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => PartnerResource::getUrl('create', [
                        'attach_user_id' => $this->getOwnerRecord()->getKey(),
                        'redirect'       => request()->fullUrl(),
                    ])),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Partner $record): string => PartnerResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Partner $record): string => PartnerResource::getUrl('edit', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}

