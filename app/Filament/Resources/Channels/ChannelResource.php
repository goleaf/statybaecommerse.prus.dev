<?php

declare(strict_types=1);

namespace App\Filament\Resources\Channels;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\Channels\Pages\CreateChannel;
use App\Filament\Resources\Channels\Pages\EditChannel;
use App\Filament\Resources\Channels\Pages\ListChannels;
use App\Filament\Resources\Channels\Schemas\ChannelForm;
use App\Filament\Resources\Channels\Tables\ChannelsTable;
use App\Models\Channel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
class ChannelResource extends Resource
{
    use HasNav;

    protected static ?string $model = Channel::class;

    public static function getNavigationIcon(): BackedEnum|\UnitEnum|Htmlable|string|null
    {
        return 'heroicon-o-rectangle-stack';
    }

    public static function form(Form $form): Form
    {
        return ChannelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return ChannelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\CustomerManagementResource\RelationManagers\OrdersRelationManager::class,
            RelationManagers\DiscountsRelationManager::class,
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListChannels::route('/'),
            'create' => CreateChannel::route('/create'),
            'edit'   => EditChannel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}