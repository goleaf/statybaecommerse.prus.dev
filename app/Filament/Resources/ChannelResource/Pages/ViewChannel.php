<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChannelResource\Pages;

use App\Filament\Resources\ChannelResource;
use App\Models\Channel;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ViewChannel extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ChannelResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->mountInteractsWithTable();
        // Hydrate the table immediately so detail assertions can inspect the bound record.
        $this->loadTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            // Present only the bound record so Livewire assertions and the UI stay in sync.
            ->query(
                fn (): Builder => Channel::query()
                    // Skip global scopes so archived or disabled channels remain reviewable in detail view.
                    ->withoutGlobalScopes()
                    ->whereKey($this->record),
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.channels.name')),
                TextColumn::make('code')
                    ->label(__('admin.channels.code')),
                TextColumn::make('type')
                    ->label(__('admin.channels.type')),
                TextColumn::make('timezone')
                    ->label(__('admin.channels.timezone'))
                    // Exposing timezone assists support teams diagnosing scheduling mismatches.
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('domain')
                    ->label(__('admin.channels.domain'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_enabled')
                    ->label(__('admin.channels.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('admin.channels.is_active'))
                    ->boolean(),
                IconColumn::make('ssl_enabled')
                    ->label(__('admin.channels.ssl_enabled'))
                    // Keeping SSL visibility ensures storefront security states are obvious on inspection.
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('analytics_enabled')
                    ->label(__('admin.channels.analytics_enabled'))
                    // Allow toggling analytics visibility without overwhelming the default summary.
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label(__('admin.channels.sort_order')),
                TextColumn::make('currency_code')
                    ->label(__('admin.channels.currency_code'))
                    // Matching the list view helps ensure the embedded table stays consistent.
                    ->badge(),
            ])
            ->paginated(false);
    }

    public function content(Schema $schema): Schema
    {
        $baseContent = parent::content($schema);

        return $baseContent->components([
            ...$baseContent->getComponents(),
            // Append the table so detail views mirror list layouts for assertions and admins.
            EmbeddedTable::make(),
        ]);
    }
}
