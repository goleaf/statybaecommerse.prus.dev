<?php declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.profile.personal_information'))
                    ->description(__('admin.profile.personal_information_description'))
                    ->components([
                        Grid::make(2)
                            ->components([
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
                            ->label(__('admin.profile.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->label(__('admin.profile.phone_number'))
                            ->tel()
                            ->maxLength(20),
                        Select::make('gender')
                            ->label(__('admin.profile.gender'))
                            ->options([
                                'male' => __('admin.gender.male'),
                                'female' => __('admin.gender.female'),
                                'other' => __('admin.gender.other'),
                            ])
                            ->native(false),
                        DatePicker::make('birth_date')
                            ->label(__('admin.profile.birth_date'))
                            ->displayFormat('Y-m-d')
                            ->maxDate(now()),
                        FileUpload::make('avatar_url')
                            ->label(__('admin.profile.avatar'))
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->directory('avatars')
                            ->visibility('public'),
                    ])
                    ->columns(2),
                Section::make(__('admin.profile.professional_information'))
                    ->description(__('admin.profile.professional_information_description'))
                    ->components([
                        TextInput::make('company')
                            ->label(__('admin.profile.company'))
                            ->maxLength(255),
                        TextInput::make('position')
                            ->label(__('admin.profile.position'))
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label(__('admin.profile.website'))
                            ->url()
                            ->maxLength(255),
                        Textarea::make('bio')
                            ->label(__('admin.profile.bio'))
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(2),
                Section::make(__('admin.profile.preferences'))
                    ->description(__('admin.profile.preferences_description'))
                    ->components([
                        Select::make('preferred_locale')
                            ->label(__('admin.profile.preferred_language'))
                            ->options([
                                'lt' => __('admin.locales.lithuanian'),
                                'en' => __('admin.locales.english'),
                            ])
                            ->native(false)
                            ->required(),
                        Select::make('timezone')
                            ->label(__('admin.profile.timezone'))
                            ->options([
                                'Europe/Vilnius' => 'Europe/Vilnius (GMT+2)',
                                'Europe/London' => 'Europe/London (GMT+0)',
                                'America/New_York' => 'America/New_York (GMT-5)',
                                'UTC' => 'UTC (GMT+0)',
                            ])
                            ->native(false)
                            ->default('Europe/Vilnius'),
                        Toggle::make('accepts_marketing')
                            ->label(__('admin.profile.accepts_marketing'))
                            ->default(false),
                        Toggle::make('two_factor_enabled')
                            ->label(__('admin.profile.two_factor_enabled'))
                            ->default(false),
                    ])
                    ->columns(2),
                Section::make(__('admin.profile.security'))
                    ->description(__('admin.profile.security_description'))
                    ->components([
                        TextInput::make('password')
                            ->label(__('admin.profile.new_password'))
                            ->password()
                            ->minLength(8)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->same('passwordConfirmation')
                            ->validationAttribute(__('admin.profile.new_password')),
                        TextInput::make('passwordConfirmation')
                            ->label(__('admin.profile.confirm_password'))
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
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
