<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Tables;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Models\DocumentTemplate;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class DocumentTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('admin.document_templates.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::resolveTypeLabel($state))
                    ->color(fn (?string $state): string => self::resolveTypeColor($state))
                    ->sortable(),
                TextColumn::make('category')
                    ->label(__('admin.document_templates.fields.category'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::resolveCategoryLabel($state))
                    ->color(fn (?string $state): string => self::resolveCategoryColor($state))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.document_templates.fields.is_active'))
                    ->sortable(),
                TextColumn::make('documents_count')
                    ->label(__('admin.document_templates.fields.documents_count'))
                    ->counts('documents')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('messages.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.document_templates.filters.type'))
                    ->options(DocumentTemplateType::options())
                    ->searchable(),
                SelectFilter::make('category')
                    ->label(__('admin.document_templates.filters.category'))
                    ->options(DocumentTemplateCategory::options())
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->label(__('admin.document_templates.filters.is_active')),
            ])
            ->actions([
                Action::make('preview_template')
                    ->label(__('admin.document_templates.actions.preview'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->button()
                    ->url(fn (DocumentTemplate $record): string => route('admin.document-templates.preview', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
                Action::make('duplicate_template')
                    ->label(__('admin.document_templates.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (DocumentTemplate $record): void {
                        $replica = $record->replicate();
                        $replica->name = self::duplicateName($record);
                        $replica->slug = self::duplicateSlug($record);
                        $replica->save();

                        Notification::make()
                            ->title(__('admin.document_templates.notifications.duplicated'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('deactivate')
                    ->label(__('admin.document_templates.actions.deactivate'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(function (Collection $records): void {
                        $records->each->update(['is_active' => false]);
                    }),
                BulkAction::make('activate')
                    ->label(__('admin.document_templates.actions.activate'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        $records->each->update(['is_active' => true]);
                    }),
                DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    private static function resolveTypeLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return __('admin.not_set');
        }

        $enum = DocumentTemplateType::tryFrom($state);

        return $enum?->label() ?? Str::headline($state);
    }

    private static function resolveTypeColor(?string $state): string
    {
        if ($state === null || $state === '') {
            return 'gray';
        }

        return DocumentTemplateType::tryFrom($state)?->color() ?? 'gray';
    }

    private static function resolveCategoryLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return __('admin.not_set');
        }

        $enum = DocumentTemplateCategory::tryFrom($state);

        return $enum?->label() ?? Str::headline($state);
    }

    private static function resolveCategoryColor(?string $state): string
    {
        if ($state === null || $state === '') {
            return 'gray';
        }

        return DocumentTemplateCategory::tryFrom($state)?->color() ?? 'gray';
    }

    private static function duplicateName(DocumentTemplate $record): string
    {
        return $record->name . ' (Copy)';
    }

    private static function duplicateSlug(DocumentTemplate $record): string
    {
        $baseSlug = $record->slug !== '' ? $record->slug : Str::slug($record->name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'template';
        $candidate = $baseSlug . '-copy';
        $suffix = 2;

        while (DocumentTemplate::query()->where('slug', $candidate)->exists()) {
            $candidate = $baseSlug . '-copy-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
