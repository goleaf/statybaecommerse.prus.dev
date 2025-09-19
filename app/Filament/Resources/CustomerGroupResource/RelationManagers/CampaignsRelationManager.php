<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CampaignsRelationManager extends RelationManager
{
    protected static string $relationship = 'targetCustomerGroups';
    protected static ?string $title = 'customer_groups.relation_campaigns';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => __('campaigns.draft'),
                        'active' => __('campaigns.active'),
                        'paused' => __('campaigns.paused'),
                        'completed' => __('campaigns.completed'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('campaigns.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('campaigns.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'paused' => 'warning',
                        'completed' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('budget')
                    ->label(__('campaigns.budget'))
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('campaigns.starts_at'))
                    ->dateTime(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('campaigns.ends_at'))
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => __('campaigns.draft'),
                        'active' => __('campaigns.active'),
                        'paused' => __('campaigns.paused'),
                        'completed' => __('campaigns.completed'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('customer_groups.attach_campaign')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('customer_groups.detach_campaign')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('customer_groups.detach_selected')),
                ]),
            ]);
    }
}


