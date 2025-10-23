<?php

declare(strict_types=1);

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

final class ViewPartner extends ViewRecord
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        // Allow quick transitions into edit mode from the read-only detail view.
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist|array
    {
        // Present the partner details in themed sections so admins see critical data at a glance.
        return $infolist->schema([
            Section::make(__('admin.partners.sections.basic_information'))
                ->schema([
                    TextEntry::make('name')
                        ->label(__('admin.partners.name'))
                        ->translateLabel(),
                    TextEntry::make('code')
                        ->label(__('admin.partners.code'))
                        ->translateLabel(),
                    TextEntry::make('tier.name')
                        ->label(__('admin.partners.tier'))
                        ->translateLabel()
                        ->placeholder('—'),
                    IconEntry::make('is_enabled')
                        ->label(__('admin.partners.is_enabled'))
                        ->translateLabel()
                        ->boolean(),
                ])
                ->columns(2),
            Section::make(__('admin.partners.sections.contact_information'))
                ->schema([
                    TextEntry::make('contact_email')
                        ->label(__('admin.partners.contact_email'))
                        ->translateLabel()
                        ->copyable(),
                    TextEntry::make('contact_phone')
                        ->label(__('admin.partners.contact_phone'))
                        ->translateLabel()
                        ->copyable(),
                ])
                ->columns(2),
            Section::make(__('admin.partners.sections.financial_settings'))
                ->schema([
                    TextEntry::make('discount_rate')
                        ->label(__('admin.partners.discount_rate'))
                        ->translateLabel()
                        ->suffix('%'),
                    TextEntry::make('commission_rate')
                        ->label(__('admin.partners.commission_rate'))
                        ->translateLabel()
                        ->suffix('%'),
                ])
                ->columns(2),
            Section::make(__('admin.common.timestamps'))
                ->schema([
                    TextEntry::make('created_at')
                        ->label(__('admin.common.created_at'))
                        ->translateLabel()
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->label(__('admin.common.updated_at'))
                        ->translateLabel()
                        ->dateTime(),
                ])
                ->columns(2),
        ]);
    }
}
