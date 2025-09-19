<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\KeyValueEntry;

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

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('key')
                            ->label('Document Key')
                            ->copyable()
                            ->copyMessage('Key copied')
                            ->copyMessageDuration(1500),

                        TextEntry::make('type')
                            ->label('Document Type')
                            ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state)
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'privacy_policy' => 'success',
                                'terms_of_use' => 'warning',
                                'refund_policy' => 'info',
                                'shipping_policy' => 'primary',
                                'cookie_policy' => 'secondary',
                                'gdpr_policy' => 'danger',
                                'legal_notice' => 'gray',
                                'imprint' => 'success',
                                'legal_document' => 'warning',
                                default => 'gray',
                            }),

                        IconEntry::make('is_enabled')
                            ->label('Enabled')
                            ->boolean(),

                        IconEntry::make('is_required')
                            ->label('Required')
                            ->boolean(),

                        TextEntry::make('sort_order')
                            ->label('Sort Order'),

                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Not published'),
                    ])
                    ->columns(2),

                Section::make('Translations')
                    ->schema([
                        RepeatableEntry::make('translations')
                            ->label('')
                            ->schema([
                                TextEntry::make('locale')
                                    ->label('Language')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'lt' => 'success',
                                        'en' => 'info',
                                        'ru' => 'warning',
                                        'de' => 'gray',
                                        default => 'secondary',
                                    }),

                                TextEntry::make('title')
                                    ->label('Title')
                                    ->weight('bold'),

                                TextEntry::make('slug')
                                    ->label('URL Slug')
                                    ->copyable()
                                    ->copyMessage('Slug copied')
                                    ->copyMessageDuration(1500),

                                TextEntry::make('content')
                                    ->label('Content')
                                    ->html()
                                    ->limit(200)
                                    ->expandable(),

                                TextEntry::make('seo_title')
                                    ->label('SEO Title')
                                    ->placeholder('Not set'),

                                TextEntry::make('seo_description')
                                    ->label('SEO Description')
                                    ->placeholder('Not set')
                                    ->limit(100)
                                    ->expandable(),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Metadata')
                    ->schema([
                        KeyValueEntry::make('meta_data')
                            ->label('')
                            ->placeholder('No metadata set'),
                    ])
                    ->collapsible(),
            ]);
    }
}