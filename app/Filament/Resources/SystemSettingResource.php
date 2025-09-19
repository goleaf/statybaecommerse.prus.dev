<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
/**
 * SystemSettingResource
 *
 * Filament v4 resource for SystemSetting management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingResource extends Resource
{
    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 18;
    protected static ?string $recordTitleAttribute = 'key';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('system_settings.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "System"->value;
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('system_settings.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('system_settings.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
    {$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string' => 'blue',
                        'integer' => 'green',
                        'float' => 'purple',
                        'boolean' => 'orange',
                        'array' => 'red',
                        'json' => 'indigo',
                        'file' => 'pink',
                        'url' => 'cyan',
                        'email' => 'teal',
                        'password' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('system_settings.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->formatStateUsing(function (string $state, SystemSetting $record): string {
                        if ($record->type === 'password') {
                            return str_repeat('*', min(strlen($state), 8));
                        }
                        if ($record->type === 'boolean') {
                            return $state ? __('system_settings.yes') : __('system_settings.no');
                        return $state;
                TextColumn::make('category')
                    ->label(__('system_settings.category'))
                    ->formatStateUsing(fn(string $state): string => __("system_settings.categories.{$state}"))
                        'general' => 'gray',
                        'appearance' => 'blue',
                        'email' => 'green',
                        'payment' => 'purple',
                        'shipping' => 'orange',
                        'security' => 'red',
                        'performance' => 'indigo',
                        'integration' => 'pink',
                        'analytics' => 'cyan',
                        'maintenance' => 'teal',
                        'custom' => 'yellow',
                TextColumn::make('group')
                    ->label(__('system_settings.group'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('default_value')
                    ->label(__('system_settings.default_value'))
                    ->limit(30)
                TextColumn::make('unit')
                    ->label(__('system_settings.unit'))
                IconColumn::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                IconColumn::make('is_required')
                    ->label(__('system_settings.is_required'))
                IconColumn::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                IconColumn::make('is_readonly')
                    ->label(__('system_settings.is_readonly'))
                IconColumn::make('is_active')
                    ->label(__('system_settings.is_active'))
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('system_settings.sort_order'))
                TextColumn::make('created_at')
                    ->label(__('system_settings.created_at'))
                    ->dateTime()
                TextColumn::make('updated_at')
                    ->label(__('system_settings.updated_at'))
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'string' => __('system_settings.types.string'),
                        'integer' => __('system_settings.types.integer'),
                        'float' => __('system_settings.types.float'),
                        'boolean' => __('system_settings.types.boolean'),
                        'array' => __('system_settings.types.array'),
                        'json' => __('system_settings.types.json'),
                        'file' => __('system_settings.types.file'),
                        'url' => __('system_settings.types.url'),
                        'email' => __('system_settings.types.email'),
                        'password' => __('system_settings.types.password'),
                    ]),
                SelectFilter::make('category')
                        'general' => __('system_settings.categories.general'),
                        'appearance' => __('system_settings.categories.appearance'),
                        'email' => __('system_settings.categories.email'),
                        'payment' => __('system_settings.categories.payment'),
                        'shipping' => __('system_settings.categories.shipping'),
                        'security' => __('system_settings.categories.security'),
                        'performance' => __('system_settings.categories.performance'),
                        'integration' => __('system_settings.categories.integration'),
                        'analytics' => __('system_settings.categories.analytics'),
                        'maintenance' => __('system_settings.categories.maintenance'),
                        'custom' => __('system_settings.categories.custom'),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('system_settings.active_only'))
                    ->falseLabel(__('system_settings.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->trueLabel(__('system_settings.public_only'))
                    ->falseLabel(__('system_settings.private_only'))
                TernaryFilter::make('is_required')
                    ->trueLabel(__('system_settings.required_only'))
                    ->falseLabel(__('system_settings.optional_only'))
                TernaryFilter::make('is_encrypted')
                    ->trueLabel(__('system_settings.encrypted_only'))
                    ->falseLabel(__('system_settings.unencrypted_only'))
                TernaryFilter::make('is_readonly')
                    ->trueLabel(__('system_settings.readonly_only'))
                    ->falseLabel(__('system_settings.editable_only'))
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('reset_to_default')
                    ->label(__('system_settings.reset_to_default'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn(SystemSetting $record): bool => !empty($record->default_value))
                    ->action(function (SystemSetting $record): void {
                        $record->update(['value' => $record->default_value]);
                        Notification::make()
                            ->title(__('system_settings.reset_to_default_success'))
                            ->success()
                            ->send();
                    ->requiresConfirmation(),
                Action::make('toggle_active')
                    ->label(fn(SystemSetting $record): string => $record->is_active ? __('system_settings.deactivate') : __('system_settings.activate'))
                    ->icon(fn(SystemSetting $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(SystemSetting $record): string => $record->is_active ? 'warning' : 'success')
                        $record->update(['is_active' => !$record->is_active]);
                            ->title($record->is_active ? __('system_settings.activated_successfully') : __('system_settings.deactivated_successfully'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('system_settings.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('system_settings.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('system_settings.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                            $records->each->update(['is_active' => false]);
                                ->title(__('system_settings.bulk_deactivated_success'))
                    BulkAction::make('reset_to_default')
                        ->label(__('system_settings.reset_selected_to_default'))
                        ->icon('heroicon-o-arrow-path')
                            $records->each(function (SystemSetting $record): void {
                                if (!empty($record->default_value)) {
                                    $record->update(['value' => $record->default_value]);
                                }
                            });
                                ->title(__('system_settings.bulk_reset_to_default_success'))
            ->defaultSort('sort_order');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view' => Pages\ViewSystemSetting::route('/{record}'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
}
