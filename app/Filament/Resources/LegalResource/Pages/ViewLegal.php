<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use App\Models\Legal;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewLegal extends ViewRecord
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {

        $infolist = $schema; // Maintain legacy naming for infolist builders while using the Schema abstraction.

        return $schema
            ->components([
                Section::make(__('legal.basic_information'))
                    ->schema([
                        TextEntry::make('key')
                            ->label(__('legal.key'))
                            ->copyable()
                            ->copyMessage('Key copied')
                            ->copyMessageDuration(1500),
                        TextEntry::make('type')
                            ->label(__('legal.type'))
                            ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state)
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'privacy_policy'  => 'success',
                                'terms_of_use'    => 'warning',
                                'refund_policy'   => 'info',
                                'shipping_policy' => 'primary',
                                'cookie_policy'   => 'secondary',
                                'gdpr_policy'     => 'danger',
                                'legal_notice'    => 'gray',
                                'imprint'         => 'success',
                                'legal_document'  => 'warning',
                                default           => 'gray',
                            }),
                        IconEntry::make('is_enabled')
                            ->label(__('legal.is_enabled'))
                            ->boolean(),
                        IconEntry::make('is_required')
                            ->label(__('legal.is_required'))
                            ->boolean(),
                        TextEntry::make('sort_order')
                            ->label(__('legal.sort_order')),
                        TextEntry::make('published_at')
                            ->label(__('legal.published_at'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder(__('legal.draft')),
                    ])
                    ->columns(2),
                Section::make(__('legal.translations'))
                    ->schema([
                        RepeatableEntry::make('translations')
                            ->schema([
                                TextEntry::make('locale')
                                    ->label(__('legal.locale'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'lt'    => 'success',
                                        'en'    => 'info',
                                        'ru'    => 'warning',
                                        'de'    => 'gray',
                                        default => 'secondary',
                                    }),
                                TextEntry::make('title')
                                    ->label(__('legal.title'))
                                    ->weight('bold'),
                                TextEntry::make('slug')
                                    ->label(__('legal.slug'))
                                    ->copyable()
                                    ->copyMessage('Slug copied')
                                    ->copyMessageDuration(1500),
                                TextEntry::make('content')
                                    ->label(__('legal.content'))
                                    ->html()
                                    ->limit(200)
                                    ->expandable(),
                                TextEntry::make('seo_title')
                                    ->label(__('legal.seo_title'))
                                    ->placeholder('Not set'),
                                TextEntry::make('seo_description')
                                    ->label(__('legal.seo_description'))
                                    ->placeholder('Not set')
                                    ->limit(100)
                                    ->expandable(),
                            ])
                            ->columns(2),
                    ]),
                Section::make(__('legal.meta_data'))
                    ->schema([
                        KeyValueEntry::make('meta_data')
                            ->label('')
                            ->placeholder(__('legal.meta_data_help')),
                    ])
                    ->collapsible(),
            ]);
    }
}
