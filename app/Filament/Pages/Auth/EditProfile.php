<?php declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.profile.personal_information'))
                    ->description(__('admin.profile.personal_information_description'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label(__('admin.profile.avatar'))
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->directory('avatars')
                            ->visibility('public')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label(__('admin.profile.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('email')
                            ->label(__('admin.profile.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('phone')
                            ->label(__('admin.profile.phone'))
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(1),
                        DatePicker::make('date_of_birth')
                            ->label(__('admin.profile.date_of_birth'))
                            ->maxDate(now()->subYears(13))
                            ->columnSpan(1),
                        Select::make('gender')
                            ->label(__('admin.profile.gender'))
                            ->options([
                                'male' => __('admin.profile.gender_male'),
                                'female' => __('admin.profile.gender_female'),
                                'other' => __('admin.profile.gender_other'),
                                'prefer_not_to_say' => __('admin.profile.gender_prefer_not_to_say'),
                            ])
                            ->columnSpan(1),
                        Select::make('timezone')
                            ->label(__('admin.profile.timezone'))
                            ->options([
                                'Europe/Vilnius' => 'Europe/Vilnius (LT)',
                                'Europe/London' => 'Europe/London (UK)',
                                'America/New_York' => 'America/New_York (US)',
                                'UTC' => 'UTC',
                            ])
                            ->default('Europe/Vilnius')
                            ->columnSpan(1),
                        Textarea::make('bio')
                            ->label(__('admin.profile.bio'))
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('admin.profile.security'))
                    ->description(__('admin.profile.security_description'))
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('admin.profile.current_password'))
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('password')
                            ->label(__('admin.profile.new_password'))
                            ->password()
                            ->rule(Password::default())
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->columnSpan(1),
                        TextInput::make('password_confirmation')
                            ->label(__('admin.profile.confirm_password'))
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->same('password')
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Section::make(__('admin.profile.preferences'))
                    ->description(__('admin.profile.preferences_description'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Select::make('locale')
                            ->label(__('admin.profile.language'))
                            ->options([
                                'lt' => __('admin.profile.language_lithuanian'),
                                'en' => __('admin.profile.language_english'),
                            ])
                            ->default('lt')
                            ->columnSpan(1),
                        Toggle::make('newsletter_subscribed')
                            ->label(__('admin.profile.newsletter'))
                            ->default(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getUrl();
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
