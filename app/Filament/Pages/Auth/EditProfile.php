<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
// use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Pages\Page as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.profile.personal_information'))
                    ->description(__('admin.profile.personal_information_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(__('admin.profile.first_name'))
                                    ->maxLength(255)
                                    ->required(),
                                TextInput::make('last_name')
                                    ->label(__('admin.profile.last_name'))
                                    ->maxLength(255)
                                    ->required(),
                            ]),
                        TextInput::make('email')
                            ->label(__('messages.admin))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->label(__('admin.profile.phone_number'))
                            ->tel()
                            ->maxLength(20),
                        Select::make('gender')
                            ->label(__('messages.admin))
                            ->options([
                                'male'   => __('messages.admin),
                                'female' => __('messages.admin),
                                'other'  => __('messages.admin),
                            ])
                            ->native(false),
                        FileUpload::make('avatar_url')
                            ->label(__('messages.admin))
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->directory('avatars')
                            ->visibility('private'),
                    ])
                    ->columns(2),
                Section::make(__('admin.profile.professional_information'))
                    ->description(__('admin.profile.professional_information_description'))
                    ->schema([
                        TextInput::make('company')
                            ->label(__('messages.admin))
                            ->maxLength(255),
                        TextInput::make('position')
                            ->label(__('messages.admin))
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label(__('messages.admin))
                            ->url()
                            ->maxLength(255),
                        Textarea::make('bio')
                            ->label(__('messages.admin))
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(2),
                Section::make(__('messages.admin))
                    ->description(__('admin.profile.preferences_description'))
                    ->schema([
                        Select::make('preferred_locale')
                            ->label(__('admin.profile.preferred_language'))
                            ->options([
                                'lt' => __('messages.admin),
                                'en' => __('messages.admin),
                            ])
                            ->native(false)
                            ->required(),
                        Select::make('timezone')
                            ->label(__('messages.admin))
                            ->options([
                                'Europe/Vilnius'   => 'Europe/Vilnius (GMT+2)',
                                'Europe/London'    => 'Europe/London (GMT+0)',
                                'America/New_York' => 'America/New_York (GMT-5)',
                                'UTC'              => 'UTC (GMT+0)',
                            ])
                            ->native(false)
                            ->default('Europe/Vilnius'),
                        Toggle::make('accepts_marketing')
                            ->label(__('admin.profile.accepts_marketing'))
                            ->default(false),
                    ])
                    ->columns(2),
                Section::make(__('messages.admin))
                    ->description(__('admin.profile.security_description'))
                    ->schema([
                        TextInput::make('password')
                            ->label(__('admin.profile.new_password'))
                            ->password()
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->same('passwordConfirmation')
                            ->validationAttribute(__('admin.profile.new_password')),
                        TextInput::make('passwordConfirmation')
                            ->label(__('admin.profile.confirm_password'))
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getUrl();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.profile.profile_updated_successfully');
    }

    public function getTitle(): string
    {
        return __('admin.profile.edit_profile');
    }

    public function getHeading(): string
    {
        return __('admin.profile.edit_profile');
    }

    public function getSubheading(): ?string
    {
        return __('admin.profile.edit_profile_subheading');
    }
}
