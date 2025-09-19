<?php declare(strict_types=1);

namespace App\Filament\Resources\Sliders;

use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Models\Slider;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

final class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | UnitEnum | null $navigationGroup | UnitEnum | protected static string | UnitEnum | null $navigationGroup | UnitEnum | protected static string | UnitEnum | null $navigationGroup | UnitEnum | protected static string | UnitEnum | null $navigationGroup | UnitEnum | protected static string | UnitEnum | null $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Slider Content')
                    ->tabs([
                        Tab::make('Lithuanian (LT)')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('translations.title') . ' (LT)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('title', $state)),
                                Textarea::make('description')
                                    ->label(__('translations.description') . ' (LT)')
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('button_text')
                                    ->label(__('translations.button_text') . ' (LT)')
                                    ->maxLength(255),
                            ]),
                        Tab::make('English (EN)')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Repeater::make('translations')
                                    ->relationship('translations')
                                    ->schema([
                                        Hidden::make('locale')
                                            ->default('en'),
                                        TextInput::make('title')
                                            ->label(__('translations.title') . ' (EN)')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label(__('translations.description') . ' (EN)')
                                            ->maxLength(1000)
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        TextInput::make('button_text')
                                            ->label(__('translations.button_text') . ' (EN)')
                                            ->maxLength(255),
                                    ])
                                    ->defaultItems(1)
                                    ->collapsible()
                                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? __('translations.new_translation'))
                                    ->addActionLabel(__('translations.add_translation'))
                                    ->deleteActionLabel(__('translations.delete_translation')),
                            ]),
                        Tab::make('Media & Styling')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('slider_image')
                                    ->label(__('translations.slider_image'))
                                    ->image()
                                    ->directory('sliders/images')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                    ->helperText(__('translations.slider_image_help')),
                                FileUpload::make('background_image')
                                    ->label(__('translations.background_image'))
                                    ->image()
                                    ->directory('sliders/backgrounds')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '21:9',
                                        '4:3',
                                    ])
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                    ->helperText(__('translations.background_image_help')),
                                ColorPicker::make('background_color')
                                    ->label(__('translations.background_color'))
                                    ->default('#ffffff')
                                    ->helperText(__('translations.background_color_help')),
                                ColorPicker::make('text_color')
                                    ->label(__('translations.text_color'))
                                    ->default('#000000')
                                    ->helperText(__('translations.text_color_help')),
                            ]),
                        Tab::make('Settings & Behavior')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                TextInput::make('button_url')
                                    ->label(__('translations.button_url'))
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText(__('translations.button_url_help')),
                                TextInput::make('sort_order')
                                    ->label(__('translations.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('translations.sort_order_help')),
                                Toggle::make('is_active')
                                    ->label(__('translations.is_active'))
                                    ->default(true)
                                    ->helperText(__('translations.is_active_help')),
                                Select::make('animation_type')
                                    ->label(__('translations.animation_type'))
                                    ->options([
                                        'fade' => __('translations.fade'),
                                        'slide' => __('translations.slide'),
                                        'zoom' => __('translations.zoom'),
                                        'flip' => __('translations.flip'),
                                    ])
                                    ->default('fade')
                                    ->live()
                                    ->helperText(__('translations.animation_type_help')),
                                TextInput::make('duration')
                                    ->label(__('translations.duration'))
                                    ->numeric()
                                    ->default(5000)
                                    ->suffix('ms')
                                    ->helperText(__('translations.duration_help')),
                                Toggle::make('autoplay')
                                    ->label(__('translations.autoplay'))
                                    ->default(true)
                                    ->helperText(__('translations.autoplay_help')),
                            ]),
                        Tab::make('Advanced Settings')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                KeyValue::make('settings')
                                    ->label(__('translations.advanced_settings'))
                                    ->keyLabel(__('translations.setting_key'))
                                    ->valueLabel(__('translations.setting_value'))
                                    ->helperText(__('translations.advanced_settings_help'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('slider_image')
                    ->label(__('translations.image'))
                    ->getStateUsing(fn(Slider $record): ?string => $record->getImageUrl('thumb'))
                    ->circular()
                    ->size(60)
                    ->defaultImageUrl(asset('images/placeholder-slider.png')),
                TextColumn::make('title')
                    ->label(__('translations.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn(Slider $record): string => $record->title),
                TextColumn::make('button_text')
                    ->label(__('translations.button_text'))
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                ColorColumn::make('background_color')
                    ->label(__('translations.background'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('text_color')
                    ->label(__('translations.text_color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('translations.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('animation_type')
                    ->label(__('translations.animation'))
                    ->getStateUsing(fn(Slider $record): string => $record->getAnimationType())
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fade' => 'gray',
                        'slide' => 'blue',
                        'zoom' => 'green',
                        'flip' => 'purple',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('duration')
                    ->label(__('translations.duration'))
                    ->getStateUsing(fn(Slider $record): string => $record->getDuration() . 'ms')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('translations.status'))
                    ->boolean()
                    ->trueLabel(__('translations.active_only'))
                    ->falseLabel(__('translations.inactive_only'))
                    ->native(false),
                SelectFilter::make('animation_type')
                    ->label(__('translations.animation_type'))
                    ->options([
                        'fade' => __('translations.fade'),
                        'slide' => __('translations.slide'),
                        'zoom' => __('translations.zoom'),
                        'flip' => __('translations.flip'),
                    ])
                    ->query(fn($query, array $data) =>
                        $query->when($data['value'], fn($q, $value) =>
                            $q->whereJsonContains('settings->animation', $value))),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('translations.edit'))
                        ->icon('heroicon-o-pencil'),
                    Action::make('duplicate')
                        ->label(__('translations.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Slider $record) {
                            $newSlider = $record->replicate();
                            $newSlider->title = $record->title . ' (Copy)';
                            $newSlider->sort_order = Slider::max('sort_order') + 1;
                            $newSlider->save();

                            // Copy translations
                            foreach ($record->translations as $translation) {
                                $newTranslation = $translation->replicate();
                                $newTranslation->slider_id = $newSlider->id;
                                $newTranslation->save();
                            }

                            Notification::make()
                                ->title(__('translations.slider_duplicated'))
                                ->success()
                                ->send();
                        }),
                    Action::make('toggle_status')
                        ->label(fn(Slider $record): string => $record->is_active
                            ? __('translations.deactivate')
                            : __('translations.activate'))
                        ->icon(fn(Slider $record): string => $record->is_active
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle')
                        ->color(fn(Slider $record): string => $record->is_active
                            ? 'danger'
                            : 'success')
                        ->action(function (Slider $record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->title($record->is_active
                                    ? __('translations.slider_activated')
                                    : __('translations.slider_deactivated'))
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make()
                        ->label(__('translations.delete'))
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('translations.delete_selected')),
                    Action::make('activate_selected')
                        ->label(__('translations.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('translations.sliders_activated'))
                                ->success()
                                ->send();
                        }),
                    Action::make('deactivate_selected')
                        ->label(__('translations.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('translations.sliders_deactivated'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->emptyStateHeading(__('translations.no_sliders'))
            ->emptyStateDescription(__('translations.no_sliders_description'))
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateActions([
                Action::make('create_slider')
                    ->label(__('translations.create_slider'))
                    ->url(route('filament.admin.resources.sliders.create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }
}
